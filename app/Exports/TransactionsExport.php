<?php

namespace App\Exports;

use App\Models\Transaction;
use App\Support\Enums\TransactionType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle,
    WithColumnFormatting,
    WithEvents
{
    public function __construct(
        private readonly string $from,
        private readonly string $to,
        private readonly ?string $projectId = null,
        private readonly ?string $type = null,
    ) {}

    public function collection()
    {
        $query = Transaction::with(['project', 'category'])
            ->dateBetween($this->from, $this->to)
            ->orderBy('transaction_date', 'desc');

        if ($this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'الوصف',
            'المشروع',
            'الفئة',
            'النوع',
            'المبلغ',
            'الملاحظات',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->transaction_date->format('Y-m-d'),
            $transaction->description,
            $transaction->project?->name ?? '—',
            $transaction->category?->name ?? '—',
            $transaction->type === TransactionType::Income ? 'دخل' : 'مصروف',
            $transaction->amount,
            $transaction->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // تنسيق رأس الجدول
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '#,##0.00',
        ];
    }

    public function title(): string
    {
        return 'المعاملات';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // RTL
                $sheet->setRightToLeft(true);

                // تنسيق تبادلي للصفوف (zebra striping)
                $highestRow = $sheet->getHighestRow();
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:G{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF8FAFC');
                    }
                }

                // حدود خارجية للجدول
                $sheet->getStyle("A1:G{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setARGB('FFE2E8F0');

                // تلوين خلايا النوع
                for ($row = 2; $row <= $highestRow; $row++) {
                    $type = $sheet->getCell("E{$row}")->getValue();
                    if ($type === 'دخل') {
                        $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FF059669');
                        $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB('FF059669');
                    } else {
                        $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FFDC2626');
                        $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB('FFDC2626');
                    }
                }

                // صف المجموع في الأسفل
                $totalRow = $highestRow + 2;
                $sheet->setCellValue("E{$totalRow}", 'الإجمالي:');
                $sheet->setCellValue("F{$totalRow}", "=SUM(F2:F{$highestRow})");
                $sheet->getStyle("E{$totalRow}:F{$totalRow}")
                    ->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("F{$totalRow}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
            },
        ];
    }
}
