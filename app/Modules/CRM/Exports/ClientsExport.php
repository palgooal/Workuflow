<?php

namespace App\Modules\CRM\Exports;

use App\Modules\CRM\Builders\ClientQueryBuilder;
use App\Modules\CRM\DTOs\ClientFiltersDTO;
use App\Modules\CRM\Enums\ClientSource;
use App\Modules\CRM\Enums\ClientStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * ClientsExport — تصدير العملاء إلى Excel
 *
 * Sprint 4 — S4.2
 * - يدعم نفس فلاتر ClientQueryBuilder
 * - RTL styling + header ملوّن
 * - حد أقصى 10,000 عميل للتصدير الفوري
 */
class ClientsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle
{
    use Exportable;

    public const MAX_SYNC_ROWS = 10_000;

    public function __construct(
        private readonly int             $userId,
        private readonly ClientFiltersDTO $filters,
    ) {}

    // ==================== Maatwebsite Contracts ====================

    public function collection(): Collection
    {
        return (new ClientQueryBuilder($this->userId))
            ->applyFilters($this->filters)
            ->toExportQuery()
            ->select([
                'clients.id',
                'clients.name',
                'clients.email',
                'clients.phone',
                'clients.company',
                'clients.position',
                'clients.status',
                'clients.source',
                'clients.city',
                'clients.country',
                'clients.total_revenue',
                'clients.total_paid',
                'clients.invoice_count',
                'clients.health_score',
                'clients.last_contact_at',
                'clients.created_at',
            ])
            ->limit(self::MAX_SYNC_ROWS)
            ->get();
    }

    public function headings(): array
    {
        return [
            'الاسم',
            'البريد الإلكتروني',
            'الهاتف',
            'الشركة',
            'المسمى الوظيفي',
            'الحالة',
            'المصدر',
            'المدينة',
            'الدولة',
            'إجمالي الإيراد',
            'المدفوع',
            'عدد الفواتير',
            'نقاط الصحة',
            'آخر تواصل',
            'تاريخ الإضافة',
        ];
    }

    public function map($client): array
    {
        return [
            $client->name,
            $client->email ?? '',
            $client->phone ?? '',
            $client->company ?? '',
            $client->position ?? '',
            $this->localizeStatus($client->status),
            $this->localizeSource($client->source),
            $client->city ?? '',
            $client->country ?? '',
            number_format((float)($client->total_revenue ?? 0), 2),
            number_format((float)($client->total_paid ?? 0), 2),
            (int)($client->invoice_count ?? 0),
            $client->health_score ?? '',
            $client->last_contact_at ? \Carbon\Carbon::parse($client->last_contact_at)->format('Y/m/d') : '',
            \Carbon\Carbon::parse($client->created_at)->format('Y/m/d'),
        ];
    }

    public function title(): string
    {
        return 'العملاء';
    }

    /**
     * تنسيق الجدول: header ملوّن + RTL + عرض تلقائي
     */
    public function styles(Worksheet $sheet): array
    {
        $sheet->setRightToLeft(true);

        $lastColumn = 'O'; // 15 عمود
        $lastRow    = $sheet->getHighestRow();

        return [
            // سطر الـ headers
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'], // indigo-600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
            // باقي الصفوف
            "A2:{$lastColumn}{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }

    // ==================== Helpers ====================

    private function localizeStatus(mixed $status): string
    {
        if ($status instanceof ClientStatus) {
            return $status->label();
        }
        $s = ClientStatus::tryFrom((string)$status);
        return $s ? $s->label() : (string)$status;
    }

    private function localizeSource(mixed $source): string
    {
        if ($source instanceof ClientSource) {
            return $source->label();
        }
        $s = ClientSource::tryFrom((string)$source);
        return $s ? $s->label() : (string)$source;
    }
}
