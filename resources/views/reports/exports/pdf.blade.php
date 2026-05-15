<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير المعاملات — {{ $from }} إلى {{ $to }}</title>
    <style>
        @font-face {
            font-family: 'Arial';
            src: local('Arial');
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            direction: rtl;
            text-align: right;
            background: #ffffff;
        }

        /* ─── Header ────────────────────────── */
        .header {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 20px 24px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .brand {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: -0.5px;
        }

        .report-meta {
            text-align: left;
            font-size: 10px;
            opacity: 0.85;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .report-period {
            font-size: 11px;
            opacity: 0.9;
        }

        /* ─── Summary Cards ─────────────────── */
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-spacing: 8px;
        }

        .summary-row {
            display: table-row;
        }

        .summary-card {
            display: table-cell;
            width: 25%;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            text-align: center;
        }

        .summary-label {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
        }

        .text-green { color: #059669; }
        .text-red   { color: #dc2626; }
        .text-blue  { color: #2563eb; }
        .text-gray  { color: #475569; }

        /* ─── Table ─────────────────────────── */
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #4f46e5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead th {
            background: #4f46e5;
            color: white;
            padding: 8px 10px;
            font-size: 10px;
            font-weight: bold;
            text-align: right;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody td {
            padding: 7px 10px;
            font-size: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-income  { background: #d1fae5; color: #065f46; }
        .badge-expense { background: #fee2e2; color: #991b1b; }

        /* ─── Footer ────────────────────────── */
        .footer {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
        }

        .page-break { page-break-after: always; }

        /* ─── Total Row ─────────────────────── */
        .total-row td {
            background: #eef2ff !important;
            font-weight: bold;
            font-size: 11px;
            color: #1e293b;
            border-top: 2px solid #4f46e5;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="header-top">
            <div>
                <div class="brand">Workuflow</div>
                <div style="font-size:10px; opacity:0.8;">منصة إدارة الأعمال والمالية</div>
            </div>
            <div class="report-meta">
                <div>تاريخ الإنشاء: {{ now()->format('Y-m-d H:i') }}</div>
                <div>{{ auth()->user()->name }}</div>
            </div>
        </div>
        <div class="report-title">تقرير المعاملات المالية</div>
        <div class="report-period">الفترة: {{ $from }} — {{ $to }}</div>
    </div>

    {{-- Summary --}}
    <div class="summary">
        <div class="summary-row">
            <div class="summary-card">
                <div class="summary-label">إجمالي الدخل</div>
                <div class="summary-value text-green">
                    {{ number_format($summary['income'], 2) }}
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-label">إجمالي المصروفات</div>
                <div class="summary-value text-red">
                    {{ number_format($summary['expenses'], 2) }}
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-label">صافي الربح</div>
                <div class="summary-value {{ $summary['net'] >= 0 ? 'text-green' : 'text-red' }}">
                    {{ number_format($summary['net'], 2) }}
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-label">عدد المعاملات</div>
                <div class="summary-value text-blue">{{ $summary['count'] }}</div>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="section-title">تفاصيل المعاملات</div>

    <table>
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
                    <td colspan="6" style="text-align:center; color:#94a3b8; padding:20px;">
                        لا توجد معاملات في هذه الفترة
                    </td>
                </tr>
            @endforelse

            {{-- Total --}}
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
        تم إنشاء هذا التقرير تلقائياً بواسطة Workuflow — {{ config('app.url') }}<br>
        جميع الأرقام بالعملة الافتراضية للحساب ({{ auth()->user()->currency }})
    </div>

</body>
</html>
