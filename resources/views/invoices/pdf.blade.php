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
            background-color: #4f46e5;
            color: #fff;
            padding: 18px 20px;
            margin-bottom: 20px;
        }
        .header-title { font-size: 20pt; font-weight: bold; }
        .header-sub { font-size: 10pt; opacity: 0.85; margin-top: 2px; }
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { padding: 4px 0; font-size: 10pt; }
        .meta-table .label { color: #64748b; width: 120px; }
        .meta-table .value { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 9pt;
            font-weight: bold;
        }
        .badge-draft    { background: #f1f5f9; color: #475569; }
        .badge-sent     { background: #dbeafe; color: #1d4ed8; }
        .badge-paid     { background: #dcfce7; color: #16a34a; }
        .badge-overdue  { background: #fee2e2; color: #dc2626; }
        .badge-cancelled{ background: #f1f5f9; color: #94a3b8; }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #4f46e5;
            border-bottom: 1px solid #e2e8f0;
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
            background: #f8fafc;
            padding: 8px 10px;
            text-align: right;
            font-weight: bold;
            border-bottom: 2px solid #e2e8f0;
        }
        table.items td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .totals { width: 280px; margin-right: auto; margin-left: 0; }
        .totals table { width: 100%; font-size: 10pt; }
        .totals td { padding: 5px 8px; }
        .totals .total-row { font-weight: bold; font-size: 12pt; background: #f8fafc; }
        .notes {
            margin-top: 16px;
            padding: 10px;
            background: #f8fafc;
            border-right: 3px solid #4f46e5;
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
    </style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-title">فاتورة</div>
    <div class="header-sub"># {{ $invoice->number }}</div>
</div>

{{-- Meta Info --}}
<table class="meta-table">
    <tr>
        <td class="label">العميل</td>
        <td class="value">{{ $invoice->client->name }}
            @if($invoice->client->company) — {{ $invoice->client->company }} @endif
        </td>
        <td style="width:40px"></td>
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

{{-- Items --}}
<div class="section-title">بنود الفاتورة</div>
<table class="items">
    <thead>
        <tr>
            <th>الوصف</th>
            <th style="width:80px; text-align:center">الكمية</th>
            <th style="width:100px; text-align:center">سعر الوحدة</th>
            <th style="width:110px; text-align:center">الإجمالي</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td style="text-align:center">{{ $item->quantity }}</td>
            <td style="text-align:center">{{ number_format($item->unit_price, 2) }}</td>
            <td style="text-align:center">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Totals --}}
<div class="totals">
    <table>
        <tr>
            <td>المجموع الفرعي</td>
            <td style="text-align:left">{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</td>
        </tr>
        @if($invoice->discount > 0)
        <tr>
            <td>الخصم</td>
            <td style="text-align:left">- {{ number_format($invoice->discount, 2) }} {{ $invoice->currency }}</td>
        </tr>
        @endif
        @if($invoice->tax > 0)
        <tr>
            <td>الضريبة</td>
            <td style="text-align:left">{{ number_format($invoice->tax, 2) }} {{ $invoice->currency }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>الإجمالي</td>
            <td style="text-align:left">{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
        </tr>
    </table>
</div>

@if($invoice->notes)
<div class="notes">
    <strong>ملاحظات:</strong> {{ $invoice->notes }}
</div>
@endif

<div class="footer">
    تم إنشاء هذه الفاتورة بواسطة {{ $invoice->user->name ?? config('app.name') }}
</div>

</body>
</html>
