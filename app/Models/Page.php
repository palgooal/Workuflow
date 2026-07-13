<?php

namespace App\Models;

use App\Support\Enums\PageFooterGroup;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Page — نظام إدارة المحتوى المصغّر لدراهم.
 *
 * يغطي الصفحات العامة/التسويقية/القانونية القابلة للنشر في الموقع، بما
 * فيها الصفحات القانونية الأربع المُرحَّلة (راجع LegalPagesSeeder).
 *
 * ملاحظة تصميم: هذا الموديل الوحيد في المشروع الذي يستخدم ULID كمفتاح
 * أساسي فعلي (وليس عمود ulid ثانوي مثل Invoice/Quote) — طُلب صراحة بهذا
 * الشكل عند بناء نظام الصفحات.
 */
class Page extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'page_type',
        'content',
        'excerpt',
        'status',
        'show_in_footer',
        'footer_group',
        'footer_label',
        'sort_order',
        'meta_title',
        'meta_description',
        'og_description',
        'document_version',
        'published_at',
        'last_reviewed_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'page_type'        => PageType::class,
            'status'           => PageStatus::class,
            'footer_group'     => PageFooterGroup::class,
            'show_in_footer'   => 'boolean',
            'sort_order'       => 'integer',
            'published_at'     => 'datetime',
            'last_reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Page $page) {
            if (empty($page->created_by) && Auth::check()) {
                $page->created_by = Auth::id();
            }
        });

        static::saving(function (Page $page) {
            if (Auth::check()) {
                $page->updated_by = Auth::id();
            }
        });

        // مسح Cache الصفحة/الفوتر عند أي تغيير يؤثر على العرض العام
        static::saved(fn (Page $page) => $page->flushPublicCaches());
        static::deleted(fn (Page $page) => $page->flushPublicCaches());
    }

    // ==================== العلاقات ====================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(PageRevision::class)->latest('id');
    }

    // ==================== Scopes ====================

    public function scopePublished($query)
    {
        return $query->where('status', PageStatus::Published->value);
    }

    public function scopeInFooter($query)
    {
        return $query->where('show_in_footer', true)
            ->where('footer_group', '!=', PageFooterGroup::None->value);
    }

    // ==================== Versioning (الصفحات القانونية) ====================

    public function isPublishedLegalPage(): bool
    {
        return $this->page_type === PageType::Legal
            && $this->status === PageStatus::Published
            && $this->exists;
    }

    /** يتطلب تدوين سبب التعديل عند إعادة نشر تعديل على صفحة قانونية منشورة أصلاً */
    public function requiresChangeNote(): bool
    {
        return $this->isPublishedLegalPage();
    }

    /** يحفظ لقطة من الحالة **الحالية قبل التعديل** في page_revisions */
    public function snapshotRevision(?string $changeNote, ?int $changedBy): PageRevision
    {
        return $this->revisions()->create([
            'version'      => $this->document_version,
            'title'        => $this->title,
            'content'      => $this->content,
            'meta_title'   => $this->meta_title,
            'meta_description' => $this->meta_description,
            'changed_by'   => $changedBy,
            'change_note'  => $changeNote,
            'published_at' => $this->published_at,
        ]);
    }

    /** يحسب رقم النسخة القانونية التالي (Minor bump بسيط: 1.0.0 → 1.1.0) */
    public function nextDocumentVersion(): string
    {
        $current = $this->document_version ?: '1.0.0';
        $parts   = array_pad(explode('.', $current), 3, '0');
        $major   = (int) $parts[0];
        $minor   = (int) $parts[1] + 1;

        return "{$major}.{$minor}.0";
    }

    /** لا يُسمح بالحذف النهائي لصفحة قانونية سبق نشرها — Archive فقط (تُطبَّق أيضاً عبر PagePolicy) */
    public function canBeForceDeleted(): bool
    {
        return ! ($this->page_type === PageType::Legal && $this->published_at !== null);
    }

    /**
     * الرابط العام الصحيح للصفحة — الصفحات القانونية الأربع المعروفة تُحل
     * دائماً لروابطها الرسمية المخصصة (/legal/...)، وأي صفحة أخرى تُحل عبر
     * الرابط العام /pages/{slug}. تُستخدَم في الفوتر الديناميكي.
     */
    public function footerUrl(): string
    {
        if ($this->page_type === PageType::Legal) {
            $known = match ($this->slug) {
                'privacy-policy'   => 'legal.privacy',
                'terms-of-service' => 'legal.terms',
                'cookie-policy'    => 'legal.cookies',
                'data-deletion'    => 'legal.data-deletion',
                default            => null,
            };

            if ($known) {
                return route($known);
            }
        }

        return route('pages.show', $this->slug);
    }

    // ==================== Cache ====================

    public function flushPublicCaches(): void
    {
        Cache::forget("page:{$this->slug}");
        Cache::forget('footer:pages');
    }

    /** الصفحة المنشورة عبر الـslug، مع Cache */
    public static function findPublishedBySlug(string $slug): ?self
    {
        return Cache::remember("page:{$slug}", now()->addHour(), function () use ($slug) {
            return static::query()
                ->published()
                ->where('slug', $slug)
                ->first();
        });
    }

    /**
     * روابط الفوتر الديناميكية: كل الصفحات المنشورة الظاهرة في الفوتر،
     * مجمَّعة حسب footer_group ومرتَّبة بـsort_order — استعلام واحد مع Cache.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, self>>
     */
    public static function footerLinks(): \Illuminate\Support\Collection
    {
        return Cache::remember('footer:pages', now()->addHour(), function () {
            return static::query()
                ->published()
                ->inFooter()
                ->orderBy('sort_order')
                ->get(['id', 'title', 'slug', 'page_type', 'footer_group', 'footer_label', 'sort_order'])
                ->groupBy(fn (Page $page) => $page->footer_group->value);
        });
    }
}
