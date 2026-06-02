<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة {{ $invoice->number }}</title>
    @php
        $userId  = $invoice->user_id;
        $color   = \App\Models\Setting::get("invoice_color_{$userId}", '#4f46e5');
        $company = \App\Models\Setting::get("invoice_company_name_{$userId}", $invoice->user->name ?? '');
        $info    = \App\Models\Setting::get("invoice_company_info_{$userId}", '');
        $footer  = \App\Models\Setting::get("invoice_footer_{$userId}", '');
        $logoPath = \App\Models\Setting::get("invoice_logo_{$userId}");
    @endphp
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background:#f4f4f5; direction:rtl; color:#1e293b; }
        .wrapper { max-width:700px; margin:32px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,.1); }
        .header { background:{{ $color }}; padding:28px 32px; display:flex; align-items:center; gap:16px; }
        .header-logo img { height:56px; width:auto; object-fit:contain; }
        .header-initials { width:52px; height:52px; background:rgba(255,255,255,.2); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:bold; color:#fff; }
        .header-text { flex:1; }
        .header-text h1 { color:#fff; font-size:20px; font-weight:700; }
        .header-text p { color:rgba(255,255,255,.8); font-size:12px; margin-top:2px; }
        .header-company { text-align:left; color:rgba(255,255,255,.85); font-size:12px; line-height:1.6; }
        .header-company strong { display:block; font-size:14px; margin-bottom:2px; }
        .body { padding:32px; }
        .meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px; }
        .meta-card { background:#f8fafc; border-radius:12px; padding:14px 16px; }
        .meta-card .label { font-size:11px; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
        .meta-card .value { font-size:15px; font-weight:600; color:#1e293b; }
        .badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
        .badge-draft    { background:#f1f5f9; color:#475569; }
        .badge-sent     { background:#dbeafe; color:#1d4ed8; }
        .badge-paid     { background:#dcfce7; color:#16a34a; }
        .badge-overdue  { background:#fee2e2; color:#dc2626; }
        .badge-cancelled{ background:#f1f5f9; color:#94a3b8; }
        .section-title { font-size:13px; font-weight:600; color:{{ $color }}; border-bottom:2px solid {{ $color }}; padding-bottom:8px; margin-bottom:12px; }
        table.items { width:100%; border-collapse:collapse; font-size:14px; }
        table.items th { text-align:right; padding:10px 12px; background:#f8fafc; color:{{ $color }}; font-weight:600; border-bottom:2px solid {{ $color }}; }
        table.items td { padding:10px 12px; border-bottom:1px solid #f1f5f9; }
        table.items tr:last-child td { border-bottom:none; }
        .totals { display:flex; justify-content:flex-start; margin-top:16px; }
        .totals-box { min-width:240px; font-size:14px; }
        .totals-box .row { display:flex; justify-content:space-between; padding:6px 0; color:#64748b; }
        .totals-box .total { display:flex; justify-content:space-between; padding:10px 0; font-size:17px; font-weight:700; color:{{ $color }}; border-top:2px solid {{ $color }}; margin-top:4px; }
        .notes { margin-top:20px; padding:12px 16px; background:#f8fafc; border-right:3px solid {{ $color }}; font-size:13px; color:#475569; border-radius:0 8px 8px 0; }
        .footer-section { margin-top:28px; padding-top:20px; border-top:1px solid #e2e8f0; text-align:center; }
        .footer-custom { font-size:14px; color:#475569; margin-bottom:6px; }
        .footer-brand { font-size:12px; color:#94a3b8; }
        @media (max-width:600px) {
            .meta-grid { grid-template-columns:1fr; }
            .header { flex-wrap:wrap; }
            .header-company { margin-top:8px; text-align:right; }
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        @if($logoPath && file_exists(storage_path('app/public/' . $logoPath)))
            <div class="header-logo"><img src="{{ asset('storage/' . $logoPath) }}" alt="Logo"></div>
        @else
            <div class="header-initials">{{ mb_substr($company ?: 'W', 0, 1) }}</div>
        @endif
        <div class="header-text">
            <h1>{{ $company ?: $invoice->user->name }}</h1>
            <p>فاتورة رقم {{ $invoice->number }}</p>
        </div>
        @if($info)
        <div class="header-company">
            <strong>{{ $company }}</strong>
            {!! nl2br(e($info)) !!}
        </div>
        @endif
    </div>

    <div class="body">

        {{-- Meta --}}
        <div class="meta-grid">
            <div class="meta-card">
                <div class="label">العميل</div>
                <div class="value">{{ $invoice->client->name }}</div>
                @if($invoice->client->company)
                <div style="font-size:12px;color:#64748b;margin-top:2px">{{ $invoice->client->company }}</div>
                @endif
            </div>
            <div class="meta-card">
                <div class="label">حالة الفاتورة</div>
                <div class="value">
                    <span class="badge badge-{{ strtolower($invoice->status->value) }}">
                        {{ $invoice->status->icon() }} {{ $invoice->status->label() }}
                    </span>
                </div>
            </div>
            <div class="meta-card">
                <div class="label">تاريخ الإصدار</div>
                <div class="value">{{ $invoice->issue_date?->format('Y/m/d') ?? '—' }}</div>
            </div>
            <div class="meta-card">
                <div class="label">تاريخ الاستحقاق</div>
                <div class="value" style="{{ $invoice->isOverdue() ? 'color:#dc2626' : '' }}">
                    {{ $invoice->due_date?->format('Y/m/d') ?? '—' }}
                    @if($invoice->isOverdue()) <span style="font-size:12px">⚠️ متأخرة</span> @endif
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="section-title">بنود الفاتورة</div>
        <table class="items">
            <thead>
                <tr>
                    <th>الوصف</th>
                    <th style="width:80px;text-align:center">الكمية</th>
                    <th style="width:120px;text-align:center">سعر الوحدة</th>
                    <th style="width:130px;text-align:center">الإجمالي</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align:center">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align:center">{{ number_format($item->unit_price, 2) }} {{ $invoice->currency }}</td>
                    <td style="text-align:center;font-weight:600">{{ number_format($item->quantity * $item->unit_price, 2) }} {{ $invoice->currency }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <div class="totals-box">
                @if($invoice->discount > 0)
                <div class="row"><span>المجموع الفرعي</span><span>{{ number_format($invoice->subtotal, 2) }} {{ $invoice->currency }}</span></div>
                <div class="row"><span>الخصم</span><span>- {{ number_format($invoice->discount, 2) }} {{ $invoice->currency }}</span></div>
                @endif
                @if($invoice->tax > 0)
                <div class="row"><span>الضريبة</span><span>{{ number_format($invoice->tax, 2) }} {{ $invoice->currency }}</span></div>
                @endif
                <div class="total"><span>الإجمالي</span><span>{{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</span></div>
            </div>
        </div>

        {{-- Notes --}}
        @if($invoice->notes)
        <div class="notes"><strong>ملاحظات:</strong> {{ $invoice->notes }}</div>
        @endif

        {{-- Footer --}}
        <div class="footer-section">
            @if($footer)<div class="footer-custom">{{ $footer }}</div>@endif
            <div class="footer-brand">دراهم — منصة المستقلين المالية</div>
        </div>

    </div>
</div>
</body>
</html>
