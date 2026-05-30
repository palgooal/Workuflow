<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin:0; padding:0; background:#f4f4f5; font-family: Arial, 'Segoe UI', sans-serif; direction:rtl; }
        .wrapper { max-width:600px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .header { background:linear-gradient(135deg,#6366f1,#4f46e5); padding:28px 32px; text-align:center; }
        .header h1 { color:#fff; margin:0; font-size:22px; font-weight:700; letter-spacing:.5px; }
        .header p  { color:#c7d2fe; margin:4px 0 0; font-size:13px; }
        .body   { padding:32px; color:#374151; font-size:15px; line-height:1.7; }
        .body p  { margin:0 0 14px; }
        .body ul { padding-right:20px; margin:0 0 14px; }
        .body a  { color:#6366f1; }
        .body a[style*="background"] { color:#fff !important; }
        .footer { background:#f9fafb; padding:20px 32px; text-align:center; border-top:1px solid #e5e7eb; }
        .footer p { margin:0; font-size:12px; color:#9ca3af; }
        .footer strong { color:#6366f1; }
    </style>
</head>
<body>
<div class="wrapper">
    {{-- Header --}}
    <div class="header">
        <h1>دراهم</h1>
        <p>منصتك المالية الذكية</p>
    </div>

    {{-- Body --}}
    <div class="body">
        {!! $body !!}
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>هذا البريد أُرسل تلقائياً من <strong>دراهم</strong> — يرجى عدم الرد عليه.</p>
        <p style="margin-top:6px;">© {{ date('Y') }} دراهم. جميع الحقوق محفوظة.</p>
    </div>
</div>
</body>
</html>
