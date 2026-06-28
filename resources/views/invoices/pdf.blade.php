@php
    $userId  = $invoice->user_id;
    $color   = \App\Models\Setting::get("invoice_color_{$userId}", '#4f46e5');
    $company = \App\Models\Setting::get("invoice_company_name_{$userId}", $invoice->user->name ?? '');
    $info    = \App\Models\Setting::get("invoice_company_info_{$userId}", '');
    $footer  = \App\Models\Setting::get("invoice_footer_{$userId}", '');
    $logoPath = \App\Models\Setting::get("invoice_logo_{$userId}");
    $logoUrl  = $logoPath ? storage_path('app/public/' . $logoPath) : null;

    // تحويل اللون لـ RGB للمعاينة الفاتحة في الجدول
    [$r, $g, $b] = sscanf($color, '#%02x%02x%02x');
    $lightColor  = sprintf('rgba(%d,%d,%d,0.08)', $r, $g, $b);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>فاتورة {{ $invoice->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 11pt;
            color: #1e293b;
            direction: rtl;
            text-align: right;
        }
        .header {
            background-color: {{ $color }};
            color: #fff;
            padding: 20px 22px;
            margin-bottom: 22px;
        }
        .header-inner { display: table; width: 100%; }
        .header-logo  { display: table-cell; vertical-align: middle; width: 70px; }
        .header-logo img { max-height: 55px; max-width: 65px; }
        .header-logo .initials {
            width: 50px; height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            font-size: 22pt;
            font-weight: bold;
            color: #fff;
        }
        .header-text  { display: table-cell; vertical-align: middle; padding-right: 12px; }
        .header-title { font-size: 18pt; font-weight: bold; }
        .header-sub   { font-size: 10pt; opacity: 0.8; margin-top: 2px; }
        .header-company { display: table-cell; vertical-align: middle; text-align: left; font-size: 9pt; opacity: 0.85; }

        .meta-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .meta-table td { padding: 4px 6px; font-size: 10pt; }
        .meta-table .label { color: #64748b; width: 130px; }
        .meta-table .value { font-weight: bold; }

        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 9pt;
            font-weight: bold;
        }
        .badge-draft     { background: #f1f5f9; color: #475569; }
        .badge-sent      { background: #dbeafe; color: #1d4ed8; }
        .badge-paid      { background: #dcfce7; color: #16a34a; }
        .badge-overdue   { background: #fee2e2; color: #dc2626; }
        .badge-cancelled { background: #f1f5f9; color: #94a3b8; }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: {{ $color }};
            border-bottom: 2px solid {{ $color }};
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10pt;
        }
        table.items th {
            background: {{ $lightColor }};
            padding: 8px 10px;
            text-align: right;
            font-weight: bold;
            border-bottom: 2px solid {{ $color }};
            color: {{ $color }};
        }
        table.items td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        table.items tr:last-child td { border-bottom: none; }

        .totals-wrap { text-align: left; }
        .totals { display: inline-block; width: 260px; font-size: 10pt; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 5px 8px; }
        .totals .subtotal-row { color: #64748b; }
        .totals .total-row {
            font-weight: bold;
            font-size: 12pt;
            border-top: 2px solid {{ $color }};
            color: {{ $color }};
        }

        .notes {
            margin-top: 16px;
            padding: 10px 12px;
            background: {{ $lightColor }};
            border-right: 3px solid {{ $color }};
            font-size: 10pt;
            color: #475569;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .footer-custom {
            font-size: 10pt;
            color: #475569;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

{{-- ── Header ─────────────────────────────────────────── --}}
<div class="header">
    <div class="header-inner">
        <div class="header-logo">
            @if($logoUrl && file_exists($logoUrl))
                <img src="{{ $logoUrl }}" alt="Logo">
            @else
                <table><tr><td class="initials">{{ mb_substr($company ?: 'W', 0, 1) }}</td></tr></table>
            @endif
        </div>
        <div class="header-text">
            <div class="header-title">فاتورة</div>
            <div class="header-sub"># {{ $invoice->number }}</div>
        </div>
        @if($company || $info)
        <div class="header-company">
            @if($company)<strong>{{ $company }}</strong><br>@endif
            @if($info){!! nl2br(e($info)) !!}@endif
        </div>
        @endif
    </div>
</div>

{{-- ── بيانات الفاتورة ────────────────────────────────── --}}
<table class="meta-table">
    <tr>
        <td class="label">العميل</td>
        <td class="value">{{ $invoice->client->name }}
            @if($invoice->client->company) — {{ $invoice->client->company }} @endif
        </td>
        <td style="width:30px"></td>
        <td class="label">تاريخ الإصدار</td>
        <td class="value">{{ $invoice->issue_date?->format('Y/m/d') ?? '—' }}</td>
    </tr>
    <tr>
        <td class="label">الحالة</td>
        <td class="value">
            <span class="badge badge-{{ strtolower($invoice->status->value) }}">
                {{ $invoice->status->label() }}
            </span>
        </td>
        <td></td>
        <td class="label">تاريخ الاستحقاق</td>
        <td class="value">{{ $invoice->due_date?->format('Y/m/d') ?? '—' }}</td>
    </tr>
    @if($invoice->project)
    <tr>
        <td class="label">المشروع</td>
        <td class="value" colspan="4">{{ $invoice->project->name }}</td>
    </tr>
    @endif
</table>

{{-- ── بنود الفاتورة ──────────────────────────────────── --}}
<div class="section-title">بنود الفاتورة</div>
<table class="items">
    <thead>
        <tr>
            <th>الوصف</th>
            <th style="width:80px; text-align:center">الكمية</th>
            <th style="width:110px; text-align:center">سعر الوحدة</th>
            <th style="width:120px; text-align:center">الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td style="text-align:center">{{ number_format($item->quantity, 2) }}</td>
            <td style="text-align:center">{{ number_format($item->unit_price, 2) }} {{ $invoice->currency }}</td>
            <td style="text-align:center; font-weight:bold">{{ number_format($item->quantity * $item->unit_price, 2) }} {{ $invoice->currency }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ── الإجماليات ─────────────────────────────────────── --}}
<div class="totals-wrap">
    <div class="totals">
        <table>
            @if($invoice->discount > 0 || $invoice->tax_amount > 0)
            <tr class="subtotal-row">
                <td>المجموع الفرعي</td>
                <td style="text-align:left">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
            </tr>
            @endif
            @if($invoice->tax_amount > 0)
            <tr class="subtotal-row">
                <td>الضريبة ({{ number_format($invoice->tax_rate, 0) }}%)</td>
                <td style="text-align:left">{{ number_format($invoice->tax_amount, 2) }} {{ $invoice->currency }}</td>
            </tr>
            @endif
            @if($invoice->discount > 0)
            <tr class="subtotal-row">
                @if($invoice->discount_type === 'percentage')
                <td>الخصم ({{ number_format($invoice->discount, 0) }}%)</td>
                <td style="text-align:left">- {{ number_format($invoice->discount_amount, 2) }} {{ $invoice->currency }}</td>
                @else
                <td>الخصم</td>
                <td style="text-align:left">- {{ number_format($invoice->discount, 2) }} {{ $invoice->currency }}</td>
                @endif
            </tr>
            @endif
            <tr class="total-row">
                <td>الإجمالي</td>
                <td style="text-align:left">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
            </tr>
        </table>
    </div>
</div>

{{-- ── ملاحظات ─────────────────────────────────────────── --}}
@if($invoice->notes)
<div class="notes">
    <strong>ملاحظات:</strong> {{ $invoice->notes }}
</div>
@endif

{{-- ── فوتر ────────────────────────────────────────────── --}}
<div class="footer">
    @if($footer)
    <div class="footer-custom">{{ $footer }}</div>
    @endif
    <div>{{ $company ?: ($invoice->user->name ?? '') }} — تم الإنشاء بواسطة دراهم</div>
</div>

</body>
</html>
