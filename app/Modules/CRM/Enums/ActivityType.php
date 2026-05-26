<?php

namespace App\Modules\CRM\Enums;

/**
 * ActivityType — نوع نشاط العميل
 *
 * يُحدِّد كل حدث يمكن تسجيله في client_activities.
 * يُستخدم في LogClientActivityAction + timeline UI.
 */
enum ActivityType: string
{
    case InvoiceSent       = 'invoice_sent';
    case InvoicePaid       = 'invoice_paid';
    case InvoiceOverdue    = 'invoice_overdue';
    case NoteAdded         = 'note_added';
    case TagAssigned       = 'tag_assigned';
    case TagRemoved        = 'tag_removed';
    case FollowUpCreated   = 'follow_up_created';
    case FollowUpCompleted = 'follow_up_completed';
    case StatusChanged     = 'status_changed';
    case FieldUpdated      = 'field_updated';
    case PortalViewed      = 'portal_viewed';
    case ImportCreated     = 'import_created';
    case AttachmentAdded   = 'attachment_added';
    case ClientCreated     = 'client_created';

    public function label(): string
    {
        return match($this) {
            self::InvoiceSent       => 'إرسال فاتورة',
            self::InvoicePaid       => 'دفع فاتورة',
            self::InvoiceOverdue    => 'فاتورة متأخرة',
            self::NoteAdded         => 'إضافة ملاحظة',
            self::TagAssigned       => 'تعيين وسم',
            self::TagRemoved        => 'إزالة وسم',
            self::FollowUpCreated   => 'جدولة متابعة',
            self::FollowUpCompleted => 'إتمام متابعة',
            self::StatusChanged     => 'تغيير الحالة',
            self::FieldUpdated      => 'تحديث حقل',
            self::PortalViewed      => 'زيارة البوابة',
            self::ImportCreated     => 'إنشاء عبر الاستيراد',
            self::AttachmentAdded   => 'إرفاق ملف',
            self::ClientCreated     => 'إنشاء العميل',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::InvoiceSent       => '📄',
            self::InvoicePaid       => '💰',
            self::InvoiceOverdue    => '⚠️',
            self::NoteAdded         => '📝',
            self::TagAssigned       => '🏷️',
            self::TagRemoved        => '🗑️',
            self::FollowUpCreated   => '📅',
            self::FollowUpCompleted => '✅',
            self::StatusChanged     => '🔄',
            self::FieldUpdated      => '✏️',
            self::PortalViewed      => '👁️',
            self::ImportCreated     => '📥',
            self::AttachmentAdded   => '📎',
            self::ClientCreated     => '👤',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::InvoicePaid, self::FollowUpCompleted => '#10B981',   // green
            self::InvoiceOverdue                        => '#EF4444',   // red
            self::InvoiceSent                           => '#3B82F6',   // blue
            self::TagAssigned, self::TagRemoved         => '#8B5CF6',   // purple
            self::StatusChanged                         => '#F59E0B',   // amber
            default                                     => '#6B7280',   // gray
        };
    }

    /**
     * هل هذا النشاط ذو أولوية عالية؟
     * يُستخدم في الـ Timeline لإبراز الأحداث المهمة.
     */
    public function isHighPriority(): bool
    {
        return in_array($this, [
            self::InvoicePaid,
            self::InvoiceOverdue,
            self::StatusChanged,
        ]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
