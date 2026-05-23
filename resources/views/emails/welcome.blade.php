<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك في دراهم</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Tajawal', Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            direction: rtl;
            text-align: right;
        }

        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 16px;
        }

        /* ─── Header ─────────────────────────────── */
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 16px 16px 0 0;
            padding: 40px 32px;
            text-align: center;
        }

        .logo {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .logo span {
            color: #a5b4fc;
        }

        .header-subtitle {
            color: #c7d2fe;
            font-size: 15px;
        }

        /* ─── Body ───────────────────────────────── */
        .body {
            background: #ffffff;
            padding: 40px 32px;
        }

        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
        }

        .greeting span {
            color: #4f46e5;
        }

        .intro {
            font-size: 15px;
            line-height: 1.8;
            color: #475569;
            margin-bottom: 32px;
        }

        /* ─── CTA Button ─────────────────────────── */
        .btn-wrapper {
            text-align: center;
            margin-bottom: 36px;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            font-family: 'Tajawal', Arial, sans-serif;
        }

        /* ─── Features ───────────────────────────── */
        .features-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .features-grid {
            display: table;
            width: 100%;
            margin-bottom: 32px;
        }

        .feature-item {
            display: table-row;
        }

        .feature-icon {
            display: table-cell;
            width: 48px;
            padding: 10px 0;
            vertical-align: top;
        }

        .feature-icon-inner {
            width: 36px;
            height: 36px;
            background: #eef2ff;
            border-radius: 8px;
            text-align: center;
            line-height: 36px;
            font-size: 18px;
        }

        .feature-text {
            display: table-cell;
            padding: 10px 0 10px 12px;
            vertical-align: top;
        }

        .feature-name {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .feature-desc {
            font-size: 13px;
            color: #64748b;
            line-height: 1.6;
        }

        /* ─── Quick Links ────────────────────────── */
        .links-section {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 32px;
        }

        .links-title {
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 12px;
        }

        .link-item {
            display: block;
            color: #4f46e5;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 6px 0;
        }

        .link-item::before {
            content: '← ';
            color: #a5b4fc;
        }

        /* ─── Divider ────────────────────────────── */
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 24px 0;
        }

        .note {
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.7;
        }

        /* ─── Footer ─────────────────────────────── */
        .footer {
            background: #1e293b;
            border-radius: 0 0 16px 16px;
            padding: 24px 32px;
            text-align: center;
        }

        .footer-brand {
            font-size: 16px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .footer-text {
            font-size: 12px;
            color: #64748b;
            line-height: 1.6;
        }

        .footer-links {
            margin-top: 12px;
        }

        .footer-link {
            color: #94a3b8;
            text-decoration: none;
            font-size: 12px;
            margin: 0 8px;
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <div class="logo">Work<span>uflow</span></div>
        <div class="header-subtitle">منصة إدارة الأعمال والمالية</div>
    </div>

    {{-- Body --}}
    <div class="body">

        <div class="greeting">
            أهلاً وسهلاً، <span>{{ $userName }}</span> 🎉
        </div>

        <p class="intro">
            يسعدنا انضمامك إلى <strong>دراهم</strong> — منصتك الشاملة لإدارة مشاريعك
            وتتبع معاملاتك المالية باحترافية وسهولة.
            حسابك جاهز الآن، وكل أدواتك بانتظارك!
        </p>

        <div class="btn-wrapper">
            <a href="{{ $dashboardUrl }}" class="btn">ابدأ الآن — لوحة التحكم</a>
        </div>

        {{-- Features --}}
        <div class="features-title">ماذا يمكنك فعله الآن؟</div>

        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon"><div class="feature-icon-inner">📁</div></div>
                <div class="feature-text">
                    <div class="feature-name">إدارة المشاريع</div>
                    <div class="feature-desc">أنشئ مشاريعك التجارية والشخصية وتتبع أداءها المالي بدقة.</div>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><div class="feature-icon-inner">💸</div></div>
                <div class="feature-text">
                    <div class="feature-name">تسجيل المعاملات</div>
                    <div class="feature-desc">دخل ومصروفات وتحويلات — كل معاملة في مكانها الصحيح.</div>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><div class="feature-icon-inner">📊</div></div>
                <div class="feature-text">
                    <div class="feature-name">تقارير وتحليلات</div>
                    <div class="feature-desc">شاهد صورتك المالية الكاملة بمخططات تفاعلية واضحة.</div>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon"><div class="feature-icon-inner">🔔</div></div>
                <div class="feature-text">
                    <div class="feature-name">تنبيهات ذكية</div>
                    <div class="feature-desc">لن تفوتك ديون مستحقة أو تجاوز ميزانية — دراهم يتابع نيابةً عنك.</div>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="links-section">
            <div class="links-title">روابط سريعة</div>
            <a href="{{ $dashboardUrl }}" class="link-item">لوحة التحكم الرئيسية</a>
            <a href="{{ $billingUrl }}" class="link-item">خطط الاشتراك وترقية الحساب</a>
            <a href="{{ $settingsUrl }}" class="link-item">إعدادات الحساب والتفضيلات</a>
        </div>

        <hr class="divider">

        <p class="note">
            تلقيت هذا البريد لأنك سجّلت مؤخراً في دراهم.
            إذا لم تكن أنت من فعل ذلك، يمكنك تجاهل هذه الرسالة بأمان.
            لأي استفسار تواصل معنا على
            <a href="mailto:{{ config('mail.from.address') }}" style="color:#4f46e5;">{{ config('mail.from.address') }}</a>.
        </p>

    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-brand">دراهم</div>
        <div class="footer-text">
            منصة إدارة الأعمال والمالية الشخصية<br>
            © {{ date('Y') }} دراهم. جميع الحقوق محفوظة.
        </div>
        <div class="footer-links">
            <a href="{{ $dashboardUrl }}" class="footer-link">لوحة التحكم</a>
            <a href="{{ $billingUrl }}" class="footer-link">الاشتراك</a>
            <a href="{{ $settingsUrl }}" class="footer-link">الإعدادات</a>
        </div>
    </div>

</div>
</body>
</html>
