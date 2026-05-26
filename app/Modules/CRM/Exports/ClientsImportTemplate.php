<?php

namespace App\Modules\CRM\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * ClientsImportTemplate — قالب Excel للاستيراد
 *
 * يُنشئ ملف .xlsx يحتوي على:
 * - سطر headers عربي ملوّن
 * - صفين من الأمثلة
 * - RTL
 */
class ClientsImportTemplate implements
    FromArray,
    WithStyles,
    ShouldAutoSize,
    WithTitle
{
    public function array(): array
    {
        return [
            // Headers
            [
                'الاسم',
                'البريد الالكتروني',
                'الهاتف',
                'الشركة',
                'المسمى الوظيفي',
                'المصدر',
                'الحالة',
                'العنوان',
                'المدينة',
                'الدولة',
                'الموقع الالكتروني',
                'ملاحظات',
            ],
            // مثال 1
            [
                'أحمد محمد',
                'ahmed@example.com',
                '+970501234567',
                'شركة ABC',
                'مدير تقني',
                'referral',
                'prospect',
                'شارع الرشيد',
                'غزة',
                'PS',
                'https://abc.com',
                'عميل مميز',
            ],
            // مثال 2
            [
                'سارة علي',
                'sara@company.com',
                '+970599876543',
                'مؤسسة XYZ',
                'مدير تنفيذي',
                'direct',
                'active',
                '',
                'رام الله',
                'PS',
                '',
                '',
            ],
        ];
    }

    public function title(): string
    {
        return 'قالب الاستيراد';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->setRightToLeft(true);

        return [
            // سطر العناوين
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
            // صفوف الأمثلة
            '2:3' => [
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F5F3FF'],
                ],
                'font' => [
                    'color' => ['rgb' => '6B7280'],
                    'italic' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }
}
