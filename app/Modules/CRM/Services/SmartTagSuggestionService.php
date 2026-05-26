<?php

namespace App\Modules\CRM\Services;

use App\Models\Client;
use App\Modules\CRM\Actions\Tag\AssignTagAction;
use App\Modules\CRM\DTOs\CreateTagDTO;
use App\Modules\CRM\Models\ClientTag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SmartTagSuggestionService — اقتراح الوسوم الذكي (L1 — Rule-based)
 *
 * Sprint 5 — S5.1
 *
 * كل قاعدة تُقيِّم العميل وتُعيد:
 *   {slug, confidence: 0.0–1.0, reason: string}
 *
 * confidence ≥ 0.85 → يُطبَّق تلقائياً عبر applyAutoRules()
 * confidence < 0.85 → يُعرض كاقتراح فقط في الـ UI
 */
class SmartTagSuggestionService
{
    // عتبة التطبيق التلقائي
    private const AUTO_APPLY_THRESHOLD = 0.85;

    // الوسوم التي تُقترح تلقائياً (system tags slugs)
    private const SUGGESTIBLE_TAGS = [
        'vip',
        'high-value',
        'late-payer',
        'inactive',
        'new-client',
        'pending-review',
    ];

    public function __construct(
        private readonly AssignTagAction $assignTagAction,
    ) {}

    // ==================== Public API ====================

    /**
     * اقتراح وسوم لعميل واحد
     * @return array<array{slug: string, name: string, confidence: float, reason: string}>
     */
    public function suggest(Client $client): array
    {
        $suggestions = [];

        foreach ($this->getRules() as $rule) {
            $result = $rule($client);
            if ($result !== null) {
                $suggestions[] = $result;
            }
        }

        // ترتيب بالـ confidence تنازلياً
        usort($suggestions, fn ($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $suggestions;
    }

    /**
     * اقتراح وسوم لمجموعة عملاء (bulk — أكثر كفاءة)
     * @return array<int, array> indexed by client_id
     */
    public function suggestBulk(Collection $clients): array
    {
        $result = [];
        foreach ($clients as $client) {
            $result[$client->id] = $this->suggest($client);
        }
        return $result;
    }

    /**
     * تطبيق القواعد ذات الثقة العالية تلقائياً
     * يُشغَّل ليلياً من RecalculateHealthScoresCommand
     *
     * @return int عدد الوسوم المطبَّقة
     */
    public function applyAutoRules(int $userId): int
    {
        $applied = 0;

        Client::where('user_id', $userId)
              ->where('is_archived', false)
              ->with(['tags:id,slug', 'latestHealthScore'])
              ->select(['id', 'user_id', 'total_revenue', 'total_paid',
                        'invoice_count', 'last_contact_at', 'created_at', 'health_score'])
              ->chunkById(100, function ($clients) use ($userId, &$applied) {
                  foreach ($clients as $client) {
                      $suggestions = $this->suggest($client);
                      $existingSlugs = $client->tags->pluck('slug')->all();

                      foreach ($suggestions as $suggestion) {
                          // تطبيق فقط إذا: ثقة عالية + الوسم غير مُضاف مسبقاً
                          if ($suggestion['confidence'] >= self::AUTO_APPLY_THRESHOLD
                              && !in_array($suggestion['slug'], $existingSlugs)
                          ) {
                              $tag = $this->findSystemTag($suggestion['slug'], $userId);
                              if ($tag) {
                                  try {
                                      $this->assignTagAction->run($client, $tag, $userId);
                                      $applied++;
                                      Log::info("SmartTag: auto-applied '{$suggestion['slug']}' to client {$client->id}");
                                  } catch (\Throwable $e) {
                                      Log::warning("SmartTag: failed to apply '{$suggestion['slug']}' to client {$client->id}: {$e->getMessage()}");
                                  }
                              }
                          }
                      }
                  }
              });

        return $applied;
    }

    // ==================== Rules ====================

    /**
     * مجموعة القواعد — كل قاعدة تأخذ Client وتُعيد array أو null
     */
    private function getRules(): array
    {
        return [
            $this->ruleVip(),
            $this->ruleHighValue(),
            $this->ruleLatePayer(),
            $this->ruleInactive(),
            $this->ruleNewClient(),
            $this->rulePendingReview(),
        ];
    }

    /**
     * VIP: إيراد ≥ 5,000 + ≥ 5 فواتير
     */
    private function ruleVip(): \Closure
    {
        return function (Client $client): ?array {
            $revenue = (float)($client->total_revenue ?? 0);
            $invoices = (int)($client->invoice_count ?? 0);

            if ($revenue >= 5000 && $invoices >= 5) {
                $confidence = min(1.0, ($revenue / 5000) * 0.5 + ($invoices / 10) * 0.5);
                return [
                    'slug'       => 'vip',
                    'name'       => 'VIP',
                    'confidence' => round($confidence, 2),
                    'reason'     => "إيراد {$revenue} + {$invoices} فواتير",
                ];
            }
            return null;
        };
    }

    /**
     * High Value: إيراد ≥ 10,000
     */
    private function ruleHighValue(): \Closure
    {
        return function (Client $client): ?array {
            $revenue = (float)($client->total_revenue ?? 0);

            if ($revenue >= 10_000) {
                $confidence = min(1.0, 0.7 + ($revenue / 50_000) * 0.3);
                return [
                    'slug'       => 'high-value',
                    'name'       => 'High Value',
                    'confidence' => round($confidence, 2),
                    'reason'     => "إيراد إجمالي " . number_format($revenue, 0),
                ];
            }
            return null;
        };
    }

    /**
     * Late Payer: نسبة الدفع < 70%
     */
    private function ruleLatePayer(): \Closure
    {
        return function (Client $client): ?array {
            $revenue = (float)($client->total_revenue ?? 0);
            if ($revenue <= 0) return null;

            $paid     = (float)($client->total_paid ?? 0);
            $payRate  = $paid / $revenue;

            if ($payRate < 0.70) {
                // كلما قلت نسبة الدفع، ارتفعت الثقة
                $confidence = min(1.0, (0.70 - $payRate) / 0.70 + 0.3);
                return [
                    'slug'       => 'late-payer',
                    'name'       => 'Late Payer',
                    'confidence' => round($confidence, 2),
                    'reason'     => 'معدل الدفع ' . round($payRate * 100) . '%',
                ];
            }
            return null;
        };
    }

    /**
     * Inactive: لا تواصل منذ 90+ يوم
     */
    private function ruleInactive(): \Closure
    {
        return function (Client $client): ?array {
            if (!$client->last_contact_at) {
                // لا تواصل على الإطلاق — لكن قد يكون عميلاً جديداً
                $daysSinceCreation = now()->diffInDays($client->created_at);
                if ($daysSinceCreation < 14) return null; // جديد جداً

                return [
                    'slug'       => 'inactive',
                    'name'       => 'Inactive',
                    'confidence' => 0.80,
                    'reason'     => 'لا سجل تواصل مسجّل',
                ];
            }

            $days = (int) now()->diffInDays($client->last_contact_at);
            if ($days >= 90) {
                $confidence = min(1.0, 0.5 + ($days / 365) * 0.5);
                return [
                    'slug'       => 'inactive',
                    'name'       => 'Inactive',
                    'confidence' => round($confidence, 2),
                    'reason'     => "آخر تواصل منذ {$days} يوماً",
                ];
            }
            return null;
        };
    }

    /**
     * New Client: أُضيف منذ ≤ 30 يوم
     */
    private function ruleNewClient(): \Closure
    {
        return function (Client $client): ?array {
            $days = (int) now()->diffInDays($client->created_at);

            if ($days <= 30) {
                // ثقة تتناقص مع الوقت: يوم 1 = 1.0, يوم 30 = 0.85
                $confidence = max(0.85, 1.0 - ($days / 30) * 0.15);
                return [
                    'slug'       => 'new-client',
                    'name'       => 'New Client',
                    'confidence' => round($confidence, 2),
                    'reason'     => "أُضيف منذ {$days} " . ($days === 1 ? 'يوم' : 'يوم'),
                ];
            }
            return null;
        };
    }

    /**
     * Pending Review: health_score < 40 (Poor) + إيراد موجود
     */
    private function rulePendingReview(): \Closure
    {
        return function (Client $client): ?array {
            $score   = $client->health_score;
            $revenue = (float)($client->total_revenue ?? 0);

            if ($score !== null && $score < 40 && $revenue > 0) {
                return [
                    'slug'       => 'pending-review',
                    'name'       => 'Pending Review',
                    'confidence' => 0.75,
                    'reason'     => "مؤشر صحة ضعيف ({$score}/100) مع وجود إيراد",
                ];
            }
            return null;
        };
    }

    // ==================== Helpers ====================

    private function findSystemTag(string $slug, int $userId): ?ClientTag
    {
        // أولاً: وسم النظام العام (user_id = null أو مشترك)
        return ClientTag::where('slug', $slug)
                        ->where(function ($q) use ($userId) {
                            $q->where('user_id', $userId)
                              ->orWhereNull('user_id');
                        })
                        ->first();
    }
}
