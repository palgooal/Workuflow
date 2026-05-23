<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير المعاملات</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 11pt;
            color: #1e293b;
            direction: rtl;
            text-align: right;
        }

        /* ─── Header ───────────────────────── */
        .header {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 18px 20px;
            margin-bottom: 16px;
        }

        .header-inner {
            width: 100%;
        }

        .brand {
            font-size: 20pt;
            font-weight: bold;
            color: #ffffff;
        }

        .brand-sub {
            font-size: 9pt;
            color: #c7d2fe;
            margin-top: 2px;
        }

        .report-meta {
            font-size: 9pt;
            color: #e0e7ff;
            margin-top: 10px;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            color: #ffffff;
            margin-top: 10px;
        }

        .report-period {
            font-size: 10pt;
            color: #c7d2fe;
            margin-top: 4px;
        }

        /* ─── Summary Table ─────────────────── */
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px;
            margin-bottom: 16px;
        }

        .summary-cell {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            text-align: center;
            width: 25%;
        }

        .summary-label {
            font-size: 9pt;
            color: #64748b;
            display: block;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 13pt;
            font-weight: bold;
        }

        .green  { color: #059669; }
        .red    { color: #dc2626; }
        .blue   { color: #2563eb; }

        /* ─── Section Title ─────────────────── */
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e293b;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        /* ─── Transactions Table ────────────── */
        .tx-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .tx-table thead th {
            background-color: #4f46e5;
            color: #ffffff;
            padding: 7px 10px;
            font-size: 10pt;
            text-align: right;
            border: 1px solid #4338ca;
        }

        .tx-table tbody td {
            padding: 6px 10px;
            font-size: 9.5pt;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .tx-table tbody tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .badge {
            padding: 2px 7px;
            font-size: 8.5pt;
            font-weight: bold;
        }

        .badge-income  { color: #065f46; background-color: #d1fae5; }
        .badge-expense { color: #991b1b; background-color: #fee2e2; }

        .total-row td {
            background-color: #eef2ff !important;
            font-weight: bold;
            font-size: 11pt;
            border-top: 2px solid #4f46e5;
        }

        /* ─── Footer ────────────────────────── */
        .footer {
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 8.5pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="brand">دراهم</div>
        <div class="brand-sub">منصة إدارة الأعمال والمالية</div>
        <div class="report-meta">
            تاريخ الإنشاء: {{ now()->format('Y-m-d H:i') }} &nbsp;|&nbsp; {{ auth()->user()->name }}
        </div>
        <div class="report-title">تقرير المعاملات المالية</div>
        <div class="report-period">الفترة: {{ $from }} — {{ $to }}</div>
    </div>

    {{-- Summary --}}
    <table class="summary-table">
        <tr>
            <td class="summary-cell">
                <span class="summary-label">إجمالي الدخل</span>
                <span class="summary-value green">{{ number_format($summary['income'], 2) }}</span>
            </td>
            <td class="summary-cell">
                <span class="summary-label">إجمالي المصروفات</span>
                <span class="summary-value red">{{ number_format($summary['expenses'], 2) }}</span>
            </td>
            <td class="summary-cell">
                <span class="summary-label">صافي الربح</span>
                <span class="summary-value {{ $summary['net'] >= 0 ? 'green' : 'red' }}">
                    {{ number_format($summary['net'], 2) }}
                </span>
            </td>
            <td class="summary-cell">
                <span class="summary-label">عدد المعاملات</span>
                <span class="summary-value blue">{{ $summary['count'] }}</span>
            </td>
        </tr>
    </table>

    {{-- Transactions Table --}}
    <div class="section-title">تفاصيل المعاملات</div>

    <table class="tx-table">
        <thead>
            <tr>
                <th>التاريخ</th>
                <th>الوصف</th>
                <th>المشروع</th>
                <th>الفئة</th>
                <th>النوع</th>
                <th>المبلغ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td>{{ $tx->transaction_date->format('Y-m-d') }}</td>
                    <td>{{ Str::limit($tx->description, 35) }}</td>
                    <td>{{ $tx->project?->name ?? '—' }}</td>
                    <td>{{ $tx->category?->name ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $tx->type->value === 'income' ? 'badge-income' : 'badge-expense' }}">
                            {{ $tx->type->value === 'income' ? 'دخل' : 'مصروف' }}
                        </span>
                    </td>
                    <td style="font-weight:600; {{ $tx->type->value === 'income' ? 'color:#059669' : 'color:#dc2626' }}">
                        {{ number_format($tx->amount, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#94a3b8; padding:18px;">
                        لا توجد معاملات في هذه الفترة
                    </td>
                </tr>
            @endforelse

            @if($transactions->count() > 0)
                <tr class="total-row">
                    <td colspan="4"></td>
                    <td>الإجمالي الصافي</td>
                    <td style="{{ $summary['net'] >= 0 ? 'color:#059669' : 'color:#dc2626' }}">
                        {{ number_format($summary['net'], 2) }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        تم إنشاء هذا التقرير تلقائياً بواسطة دراهم — {{ config('app.url') }}<br>
        جميع الأرقام بالعملة الافتراضية للحساب ({{ auth()->user()->currency }})
    </div>

</body>
</html>
