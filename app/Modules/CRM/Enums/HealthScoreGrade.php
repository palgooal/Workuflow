<?php

namespace App\Modules\CRM\Enums;

/**
 * HealthScoreGrade — تصنيف مؤشر صحة العميل
 *
 * يُحوِّل الدرجة الرقمية (0-100) إلى تصنيف نصي للعرض.
 * الحدود مُعرَّفة في config/crm.php → health_score.grades
 */
enum HealthScoreGrade: string
{
    case Excellent = 'excellent';  // 80+
    case Good      = 'good';       // 60-79
    case Fair      = 'fair';       // 40-59
    case Poor      = 'poor';       // 0-39

    public function label(): string
    {
        return match($this) {
            self::Excellent => 'ممتاز',
            self::Good      => 'جيد',
            self::Fair      => 'مقبول',
            self::Poor      => 'ضعيف',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Excellent => '#10B981',   // emerald
            self::Good      => '#3B82F6',   // blue
            self::Fair      => '#F59E0B',   // amber
            self::Poor      => '#EF4444',   // red
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Excellent => 'bg-emerald-100 text-emerald-800',
            self::Good      => 'bg-blue-100 text-blue-800',
            self::Fair      => 'bg-amber-100 text-amber-800',
            self::Poor      => 'bg-red-100 text-red-800',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Excellent => '🌟',
            self::Good      => '👍',
            self::Fair      => '⚠️',
            self::Poor      => '🔴',
        };
    }

    /**
     * تحديد التصنيف من الدرجة الرقمية.
     * الحدود: Excellent ≥ 80 | Good ≥ 60 | Fair ≥ 40 | Poor < 40
     */
    public static function fromScore(int $score): self
    {
        return match(true) {
            $score >= 80 => self::Excellent,
            $score >= 60 => self::Good,
            $score >= 40 => self::Fair,
            default      => self::Poor,
        };
    }

    /** الدرجة الدنيا لهذا التصنيف */
    public function minScore(): int
    {
        return match($this) {
            self::Excellent => 80,
            self::Good      => 60,
            self::Fair      => 40,
            self::Poor      => 0,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
