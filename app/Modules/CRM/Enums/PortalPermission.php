<?php

namespace App\Modules\CRM\Enums;

/**
 * PortalPermission — صلاحيات بوابة العميل
 *
 * تُخزَّن كـ JSON array في client_portal_tokens.permissions
 * مثال: ["view_invoices", "download_invoices"]
 */
enum PortalPermission: string
{
    case ViewInvoices     = 'view_invoices';
    case DownloadInvoices = 'download_invoices';
    case MakePayments     = 'make_payments';
    case ViewFiles        = 'view_files';

    public function label(): string
    {
        return match($this) {
            self::ViewInvoices     => 'عرض الفواتير',
            self::DownloadInvoices => 'تنزيل الفواتير',
            self::MakePayments     => 'إجراء الدفعات',
            self::ViewFiles        => 'عرض الملفات',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::ViewInvoices     => 'يمكن للعميل رؤية قائمة فواتيره وحالتها',
            self::DownloadInvoices => 'يمكن للعميل تنزيل الفواتير كـ PDF',
            self::MakePayments     => 'يمكن للعميل متابعة حالة دفعاته',
            self::ViewFiles        => 'يمكن للعميل عرض الملفات المشتركة معه',
        };
    }

    /** الصلاحيات الافتراضية عند إنشاء رمز جديد */
    public static function defaults(): array
    {
        return [
            self::ViewInvoices->value,
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
