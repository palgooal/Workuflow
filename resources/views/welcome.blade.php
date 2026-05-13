<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workuflow — اعرف وضعك المالي الحقيقي</title>
    <meta name="description" content="منصة SaaS مالية للمستقلين وأصحاب الأعمال. تتبع دخلك، مصروفاتك، وديونك من مكان واحد.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --indigo-50: #eef2ff;
            --indigo-100: #e0e7ff;
            --indigo-500: #6366f1;
            --indigo-600: #4f46e5;
            --indigo-700: #4338ca;
            --indigo-900: #312e81;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --emerald-500: #10b981;
            --emerald-600: #059669;
            --amber-400: #fbbf24;
            --amber-500: #f59e0b;
            --red-500: #ef4444;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Tajawal', sans-serif;
            background: #fff;
            color: var(--slate-800);
            line-height: 1.7;
            direction: rtl;
        }

        /* ─── Navbar ─── */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--slate-200);
        }
        .navbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--indigo-600);
            text-decoration: none;
        }
        .logo span { color: var(--slate-700); font-weight: 400; }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
            list-style: none;
        }
        .nav-links a {
            text-decoration: none;
            color: var(--slate-600);
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: var(--indigo-600); }
        .nav-cta { display: flex; align-items: center; gap: 0.75rem; }
        .btn-ghost {
            text-decoration: none;
            color: var(--slate-600);
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .btn-ghost:hover { background: var(--slate-100); }
        .btn-primary {
            background: var(--indigo-600);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.55rem 1.4rem;
            border-radius: 10px;
            transition: background 0.2s, transform 0.1s;
            display: inline-block;
        }
        .btn-primary:hover { background: var(--indigo-700); transform: translateY(-1px); }

        /* ─── Hero ─── */
        .hero {
            background: linear-gradient(160deg, var(--indigo-50) 0%, #fff 60%);
            padding: 6rem 1.5rem 5rem;
            text-align: center;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--indigo-100);
            color: var(--indigo-700);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.35rem 1rem;
            border-radius: 100px;
            margin-bottom: 1.75rem;
        }
        .hero h1 {
            font-size: clamp(2.2rem, 5vw, 3.6rem);
            font-weight: 900;
            color: var(--slate-900);
            line-height: 1.2;
            max-width: 820px;
            margin: 0 auto 1.25rem;
        }
        .hero h1 .accent { color: var(--indigo-600); }
        .hero p {
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            color: var(--slate-500);
            max-width: 560px;
            margin: 0 auto 2.5rem;
        }
        .hero-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn-hero {
            background: var(--indigo-600);
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.05rem;
            padding: 0.85rem 2.2rem;
            border-radius: 12px;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(99,102,241,.35);
        }
        .btn-hero:hover { background: var(--indigo-700); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(99,102,241,.4); }
        .btn-outline {
            border: 2px solid var(--slate-200);
            color: var(--slate-700);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.8rem 1.8rem;
            border-radius: 12px;
            transition: all 0.2s;
        }
        .btn-outline:hover { border-color: var(--indigo-300); color: var(--indigo-600); }
        .hero-note {
            margin-top: 1.25rem;
            font-size: 0.875rem;
            color: var(--slate-400);
        }
        .hero-note strong { color: var(--emerald-600); }

        /* Dashboard Preview */
        .dashboard-preview {
            max-width: 960px;
            margin: 4rem auto 0;
            background: var(--slate-900);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 40px 80px rgba(15,23,42,.25), 0 0 0 1px rgba(255,255,255,.05);
        }
        .preview-bar {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red { background: #ff5f57; }
        .dot-yellow { background: #febc2e; }
        .dot-green { background: #28c840; }
        .preview-title { color: var(--slate-400); font-size: 0.8rem; margin-right: auto; }
        .preview-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1rem; }
        .preview-card {
            background: var(--slate-800);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid rgba(255,255,255,.06);
        }
        .preview-card-label { font-size: 0.75rem; color: var(--slate-400); margin-bottom: 0.4rem; }
        .preview-card-value { font-size: 1.3rem; font-weight: 700; color: #fff; }
        .preview-card-change { font-size: 0.75rem; margin-top: 0.3rem; }
        .up { color: var(--emerald-500); }
        .down { color: var(--red-500); }
        .preview-chart {
            background: var(--slate-800);
            border-radius: 12px;
            padding: 1rem;
            height: 110px;
            border: 1px solid rgba(255,255,255,.06);
            display: flex;
            align-items: flex-end;
            gap: 0.4rem;
            overflow: hidden;
        }
        .bar { flex: 1; border-radius: 4px 4px 0 0; min-height: 8px; }
        .bar-income { background: var(--indigo-500); opacity: 0.85; }
        .bar-expense { background: var(--amber-500); opacity: 0.7; }

        /* ─── Sections ─── */
        .section { padding: 5rem 1.5rem; }
        .section-alt { background: var(--slate-50); }
        .container { max-width: 1200px; margin: 0 auto; }
        .section-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--indigo-600);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.75rem;
        }
        .section-title {
            font-size: clamp(1.75rem, 3.5vw, 2.5rem);
            font-weight: 800;
            color: var(--slate-900);
            line-height: 1.25;
            margin-bottom: 1rem;
        }
        .section-subtitle {
            font-size: 1.1rem;
            color: var(--slate-500);
            max-width: 560px;
        }
        .section-header { margin-bottom: 3.5rem; }
        .section-header.center { text-align: center; }
        .section-header.center .section-subtitle { margin: 0 auto; }

        /* Pain Points */
        .pain-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; }
        .pain-card {
            background: #fff;
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            padding: 1.75rem;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .pain-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.08); transform: translateY(-3px); }
        .pain-icon { font-size: 2rem; margin-bottom: 1rem; }
        .pain-card h3 { font-size: 1rem; font-weight: 700; color: var(--slate-800); margin-bottom: 0.5rem; }
        .pain-card p { font-size: 0.9rem; color: var(--slate-500); line-height: 1.6; }

        /* Features */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; }
        .feature-item { display: flex; gap: 1.25rem; align-items: flex-start; }
        .feature-icon-wrap {
            width: 48px; height: 48px;
            background: var(--indigo-50);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .feature-item h3 { font-size: 1rem; font-weight: 700; color: var(--slate-800); margin-bottom: 0.35rem; }
        .feature-item p { font-size: 0.9rem; color: var(--slate-500); line-height: 1.6; }

        /* Steps */
        .steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2rem; }
        .step { text-align: center; }
        .step-num {
            width: 56px; height: 56px;
            background: var(--indigo-600);
            color: #fff; font-size: 1.3rem; font-weight: 800;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
            box-shadow: 0 4px 16px rgba(99,102,241,.3);
        }
        .step h3 { font-size: 1rem; font-weight: 700; color: var(--slate-800); margin-bottom: 0.5rem; }
        .step p { font-size: 0.875rem; color: var(--slate-500); }

        /* Stats */
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 2rem; text-align: center; }
        .stat-value { font-size: 2.5rem; font-weight: 900; color: var(--indigo-600); line-height: 1; }
        .stat-label { font-size: 0.9rem; color: var(--slate-500); margin-top: 0.4rem; }

        /* Testimonials */
        .testimonials-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .testimonial {
            background: #fff;
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            padding: 1.75rem;
        }
        .testimonial-text { font-size: 0.95rem; color: var(--slate-600); line-height: 1.7; margin-bottom: 1.25rem; font-style: italic; }
        .testimonial-author { display: flex; align-items: center; gap: 0.75rem; }
        .testimonial-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            background: var(--indigo-100);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; font-weight: 700;
            color: var(--indigo-700);
        }
        .testimonial-name { font-weight: 700; font-size: 0.9rem; color: var(--slate-800); }
        .testimonial-role { font-size: 0.8rem; color: var(--slate-400); }
        .stars { color: var(--amber-400); font-size: 0.85rem; margin-bottom: 1rem; letter-spacing: 2px; }

        /* Pricing */
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; max-width: 980px; margin: 0 auto; }
        .pricing-card {
            background: #fff;
            border: 2px solid var(--slate-200);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.2s;
            position: relative;
        }
        .pricing-card.featured { border-color: var(--indigo-500); box-shadow: 0 8px 40px rgba(99,102,241,.2); }
        .pricing-badge {
            position: absolute; top: -14px; right: 50%; transform: translateX(50%);
            background: var(--indigo-600); color: #fff;
            font-size: 0.75rem; font-weight: 700;
            padding: 0.3rem 1.1rem; border-radius: 100px;
        }
        .pricing-plan { font-size: 0.9rem; font-weight: 700; color: var(--indigo-600); margin-bottom: 0.5rem; }
        .pricing-price { font-size: 2.75rem; font-weight: 900; color: var(--slate-900); line-height: 1; }
        .pricing-price span { font-size: 1rem; font-weight: 500; color: var(--slate-400); }
        .pricing-desc { font-size: 0.875rem; color: var(--slate-500); margin: 0.75rem 0 1.5rem; }
        .pricing-features { list-style: none; margin-bottom: 2rem; }
        .pricing-features li {
            display: flex; align-items: center; gap: 0.6rem;
            font-size: 0.9rem; color: var(--slate-600);
            padding: 0.4rem 0; border-bottom: 1px solid var(--slate-100);
        }
        .pricing-features li:last-child { border-bottom: none; }
        .check { color: var(--emerald-500); font-weight: 700; }
        .cross { color: var(--slate-300); }
        .pricing-btn {
            display: block; text-align: center; text-decoration: none;
            font-weight: 700; font-size: 0.95rem;
            padding: 0.85rem; border-radius: 12px; transition: all 0.2s;
        }
        .pricing-btn-outline { border: 2px solid var(--slate-200); color: var(--slate-700); }
        .pricing-btn-outline:hover { border-color: var(--indigo-400); color: var(--indigo-600); }
        .pricing-btn-filled { background: var(--indigo-600); color: #fff; box-shadow: 0 4px 14px rgba(99,102,241,.3); }
        .pricing-btn-filled:hover { background: var(--indigo-700); }

        /* CTA */
        .cta-section {
            background: linear-gradient(135deg, var(--indigo-600) 0%, var(--indigo-900) 100%);
            padding: 6rem 1.5rem; text-align: center; color: #fff;
        }
        .cta-section h2 { font-size: clamp(1.8rem, 4vw, 3rem); font-weight: 900; margin-bottom: 1rem; }
        .cta-section p { font-size: 1.1rem; opacity: 0.8; max-width: 480px; margin: 0 auto 2.5rem; }
        .btn-white {
            background: #fff; color: var(--indigo-700);
            text-decoration: none; font-weight: 700; font-size: 1.05rem;
            padding: 0.9rem 2.4rem; border-radius: 12px; display: inline-block;
            transition: all 0.2s; box-shadow: 0 4px 20px rgba(0,0,0,.2);
        }
        .btn-white:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.25); }
        .cta-note { margin-top: 1rem; font-size: 0.875rem; opacity: 0.6; }

        /* Footer */
        footer { background: var(--slate-900); color: var(--slate-400); padding: 3rem 1.5rem 2rem; }
        .footer-inner {
            max-width: 1200px; margin: 0 auto;
            display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem;
        }
        .footer-brand p { margin-top: 0.75rem; font-size: 0.875rem; line-height: 1.6; }
        .footer-col h4 { color: #fff; font-weight: 700; font-size: 0.9rem; margin-bottom: 1rem; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 0.5rem; }
        .footer-col ul li a { text-decoration: none; color: var(--slate-400); font-size: 0.875rem; transition: color 0.2s; }
        .footer-col ul li a:hover { color: #fff; }
        .footer-bottom {
            max-width: 1200px; margin: 2rem auto 0;
            padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,.08);
            display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .nav-links, .nav-cta .btn-ghost { display: none; }
            .preview-grid { grid-template-columns: repeat(2, 1fr); }
            .footer-inner { grid-template-columns: 1fr; gap: 2rem; }
            .footer-bottom { flex-direction: column; gap: 0.5rem; text-align: center; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="navbar-inner">
        <a href="#" class="logo">Workuflow<span>.</span></a>
        <ul class="nav-links">
            <li><a href="#features">المميزات</a></li>
            <li><a href="#how">كيف يعمل</a></li>
            <li><a href="#pricing">الأسعار</a></li>
        </ul>
        <div class="nav-cta">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-ghost">لوحة التحكم</a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">تسجيل الدخول</a>
                <a href="{{ route('register') }}" class="btn-primary">ابدأ مجاناً</a>
            @endauth
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="hero-badge">✨ لا بطاقة ائتمان مطلوبة — ابدأ مجاناً الآن</div>
    <h1>
        <span class="accent">نظّم فلوسك</span> ومشاريعك<br>
        واعرف بالضبط أين يذهب ربحك
    </h1>
    <p>منصة مالية ذكية للمستقلين وأصحاب الأعمال — تتبع دخلك، مصروفاتك، وديونك من مكان واحد دون تعقيد.</p>
    <div class="hero-actions">
        @auth
            <a href="{{ route('dashboard') }}" class="btn-hero">اذهب للوحة التحكم ←</a>
        @else
            <a href="{{ route('register') }}" class="btn-hero">ابدأ مجاناً الآن ←</a>
            <a href="#how" class="btn-outline">كيف يعمل؟</a>
        @endauth
    </div>
    <p class="hero-note"><strong>مجاني تماماً</strong> حتى ٥٠ معاملة/شهر · لا إعداد معقد · جاهز خلال دقيقة</p>

    <div class="dashboard-preview">
        <div class="preview-bar">
            <div class="dot dot-red"></div>
            <div class="dot dot-yellow"></div>
            <div class="dot dot-green"></div>
            <span class="preview-title">Workuflow — لوحة التحكم</span>
        </div>
        <div class="preview-grid">
            <div class="preview-card">
                <div class="preview-card-label">إجمالي الدخل</div>
                <div class="preview-card-value">١٢٬٤٠٠</div>
                <div class="preview-card-change up">↑ ١٨٪ عن الشهر الماضي</div>
            </div>
            <div class="preview-card">
                <div class="preview-card-label">المصروفات</div>
                <div class="preview-card-value">٤٬٨٢٠</div>
                <div class="preview-card-change down">↓ ٥٪ عن الشهر الماضي</div>
            </div>
            <div class="preview-card">
                <div class="preview-card-label">صافي الربح</div>
                <div class="preview-card-value">٧٬٥٨٠</div>
                <div class="preview-card-change up">↑ ٣١٪ عن الشهر الماضي</div>
            </div>
            <div class="preview-card">
                <div class="preview-card-label">المشاريع النشطة</div>
                <div class="preview-card-value">٦</div>
                <div class="preview-card-change" style="color:#6366f1">٣ تجارية · ٣ شخصية</div>
            </div>
        </div>
        <div class="preview-chart">
            <div class="bar bar-income" style="height:55%"></div>
            <div class="bar bar-expense" style="height:30%"></div>
            <div class="bar bar-income" style="height:70%"></div>
            <div class="bar bar-expense" style="height:40%"></div>
            <div class="bar bar-income" style="height:85%"></div>
            <div class="bar bar-expense" style="height:35%"></div>
            <div class="bar bar-income" style="height:65%"></div>
            <div class="bar bar-expense" style="height:50%"></div>
            <div class="bar bar-income" style="height:90%"></div>
            <div class="bar bar-expense" style="height:45%"></div>
            <div class="bar bar-income" style="height:75%"></div>
            <div class="bar bar-expense" style="height:38%"></div>
        </div>
    </div>
</section>

<!-- PAIN POINTS -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header center">
            <div class="section-label">تعرّف على نفسك</div>
            <h2 class="section-title">هل تعاني من هذه المشاكل يومياً؟</h2>
        </div>
        <div class="pain-grid">
            <div class="pain-card">
                <div class="pain-icon">❓</div>
                <h3>لا تعرف صافي ربحك الحقيقي</h3>
                <p>تحصل على دخل جيد لكن بعد المصروفات والالتزامات لا تعرف كم بقي في جيبك فعلاً.</p>
            </div>
            <div class="pain-card">
                <div class="pain-icon">🔔</div>
                <h3>تنسى الفواتير والالتزامات</h3>
                <p>فواتير شهرية، اشتراكات، إيجارات — تتراكم وتُنسى حتى تصبح متأخرة أو تسبب ضائقة مفاجئة.</p>
            </div>
            <div class="pain-card">
                <div class="pain-icon">🔀</div>
                <h3>تخلط الشخصي بالتجاري</h3>
                <p>لا فصل واضح بين ما تنفقه على حياتك وما تنفقه على مشاريعك — الصورة المالية مشوّهة.</p>
            </div>
            <div class="pain-card">
                <div class="pain-icon">📉</div>
                <h3>لا تعرف وضعك في نهاية الشهر</h3>
                <p>تشعر أنك تكسب جيداً لكن لا تجد ما يكفيك — لأنك لا تتابع التدفق النقدي الفعلي.</p>
            </div>
            <div class="pain-card">
                <div class="pain-icon">📊</div>
                <h3>لا توجد ميزانية واضحة</h3>
                <p>تعمل بدون خطة مالية — لا سقف للمصروفات، لا هدف للادخار، لا توقع للإيرادات.</p>
            </div>
            <div class="pain-card">
                <div class="pain-icon">💳</div>
                <h3>الديون تتراكم بلا تتبع</h3>
                <p>لا تذكر من يدين لك ومن تدين له — ولا تعرف متى تستحق هذه الديون.</p>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="section" id="features">
    <div class="container">
        <div class="section-header">
            <div class="section-label">المميزات</div>
            <h2 class="section-title">كل ما تحتاجه في مكان واحد</h2>
            <p class="section-subtitle">أدوات مالية بسيطة وفعّالة — بدون تعقيد أنظمة المحاسبة التقليدية.</p>
        </div>
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon-wrap">📊</div>
                <div>
                    <h3>لوحة تحكم ذكية</h3>
                    <p>صورة مالية فورية — دخل، مصروفات، ربح، ونمو — مع رسوم بيانية مقارنة بالشهر الماضي.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrap">📁</div>
                <div>
                    <h3>عزل مالي لكل مشروع</h3>
                    <p>أنشئ مشاريع منفصلة (شخصية / تجارية) مع تقارير مالية مستقلة لكل واحد.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrap">💸</div>
                <div>
                    <h3>تسجيل المعاملات بسهولة</h3>
                    <p>أضف دخلاً أو مصروفاً في ثوانٍ — مع فلترة متقدمة وبحث سريع وتصدير CSV.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrap">🔁</div>
                <div>
                    <h3>الالتزامات المتكررة تلقائياً</h3>
                    <p>سجّل الإيجار والاشتراكات والرواتب — يُسجّلها النظام تلقائياً في موعدها كل شهر.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrap">🎯</div>
                <div>
                    <h3>ميزانية مع تنبيهات ذكية</h3>
                    <p>حدّد سقفاً لكل فئة مصروفات — تنبيه فوري عند الاقتراب من الحد أو تجاوزه.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrap">💳</div>
                <div>
                    <h3>إدارة الديون والالتزامات</h3>
                    <p>تتبع ما عليك وما لك — مع سجل سداد جزئي وتنبيهات قبل مواعيد الاستحقاق.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrap">📈</div>
                <div>
                    <h3>تقارير بلغة بشرية</h3>
                    <p>أرباح وخسائر، تدفق نقدي، مقارنة مشاريع — بدون مصطلحات محاسبية معقدة.</p>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon-wrap">🔒</div>
                <div>
                    <h3>أمان مؤسسي</h3>
                    <p>بياناتك معزولة تلقائياً — لا يستطيع أحد الوصول لمعاملاتك أو بياناتك المالية.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="section section-alt">
    <div class="container">
        <div class="stats-row">
            <div>
                <div class="stat-value">+١٢٠٠</div>
                <div class="stat-label">مستخدم نشط</div>
            </div>
            <div>
                <div class="stat-value">+٥٠٠٠</div>
                <div class="stat-label">مشروع مُدار</div>
            </div>
            <div>
                <div class="stat-value">+٢٠٠ ألف</div>
                <div class="stat-label">معاملة مُسجَّلة</div>
            </div>
            <div>
                <div class="stat-value">٩٩.٩٪</div>
                <div class="stat-label">uptime مضمون</div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section" id="how">
    <div class="container">
        <div class="section-header center">
            <div class="section-label">كيف يعمل</div>
            <h2 class="section-title">جاهز خلال أقل من دقيقتين</h2>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-num">١</div>
                <h3>أنشئ حسابك مجاناً</h3>
                <p>سجّل بريدك الإلكتروني، اختر عملتك ومنطقتك الزمنية — لا بطاقة ائتمان مطلوبة.</p>
            </div>
            <div class="step">
                <div class="step-num">٢</div>
                <h3>أضف مشاريعك</h3>
                <p>أنشئ مشروعاً لكل مصدر دخل أو نشاط — شخصي أو تجاري، بعملة مستقلة.</p>
            </div>
            <div class="step">
                <div class="step-num">٣</div>
                <h3>سجّل معاملاتك</h3>
                <p>أضف الدخل والمصروفات بسرعة — أو اتركها للنظام يسجّلها تلقائياً.</p>
            </div>
            <div class="step">
                <div class="step-num">٤</div>
                <h3>اعرف وضعك فوراً</h3>
                <p>لوحة تحكم تخبرك بالضبط كم ربحت، كم أنفقت، وما يجب دفعه قريباً.</p>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header center">
            <div class="section-label">آراء المستخدمين</div>
            <h2 class="section-title">ماذا يقول مستخدمونا</h2>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial">
                <div class="stars">★★★★★</div>
                <p class="testimonial-text">"أخيراً أعرف كم أربح فعلاً من كل مشروع! كنت أعتقد أنني أكسب جيداً لكن اكتشفت أن ثلاثة مشاريع كانت تخسر."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">م</div>
                    <div>
                        <div class="testimonial-name">محمد السلمي</div>
                        <div class="testimonial-role">مطور ويب مستقل — الرياض</div>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <div class="stars">★★★★★</div>
                <p class="testimonial-text">"الالتزامات المتكررة غيّرت حياتي — ما عدت أنسى فاتورة واحدة. والميزانية تحذّرني قبل أن أتجاوز حدودي."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">ر</div>
                    <div>
                        <div class="testimonial-name">رنا العمري</div>
                        <div class="testimonial-role">مصممة جرافيك — الكويت</div>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <div class="stars">★★★★★</div>
                <p class="testimonial-text">"عندي ٧ مشاريع مختلفة — Workuflow يعطيني صورة واضحة لكل واحد وللصورة الكاملة في نفس الوقت. أفضل استثمار."</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">ف</div>
                    <div>
                        <div class="testimonial-name">فهد الغامدي</div>
                        <div class="testimonial-role">صاحب متجر إلكتروني — جدة</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PRICING -->
<section class="section" id="pricing">
    <div class="container">
        <div class="section-header center">
            <div class="section-label">الأسعار</div>
            <h2 class="section-title">اختر الخطة المناسبة لك</h2>
            <p class="section-subtitle">ابدأ مجاناً وانتقل عندما تحتاج — لا عقود، لا التزامات.</p>
        </div>
        <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-plan">مجاني</div>
                <div class="pricing-price">٠ <span>ر.س / شهر</span></div>
                <p class="pricing-desc">مثالي للبداية والتجربة</p>
                <ul class="pricing-features">
                    <li><span class="check">✓</span> ٢ مشاريع</li>
                    <li><span class="check">✓</span> ٥٠ معاملة / شهر</li>
                    <li><span class="check">✓</span> تقارير أساسية</li>
                    <li><span class="check">✓</span> إشعارات داخل التطبيق</li>
                    <li><span class="cross">✕</span> تصدير البيانات</li>
                    <li><span class="cross">✕</span> تقارير متقدمة</li>
                    <li><span class="cross">✕</span> API Access</li>
                </ul>
                @auth
                    <a href="{{ route('dashboard') }}" class="pricing-btn pricing-btn-outline">لوحة التحكم</a>
                @else
                    <a href="{{ route('register') }}" class="pricing-btn pricing-btn-outline">ابدأ مجاناً</a>
                @endauth
            </div>

            <div class="pricing-card featured">
                <div class="pricing-badge">الأكثر شيوعاً</div>
                <div class="pricing-plan">Pro ⚡</div>
                <div class="pricing-price">٩٩ <span>ر.س / شهر</span></div>
                <p class="pricing-desc">للمستقلين وأصحاب الأعمال النامية</p>
                <ul class="pricing-features">
                    <li><span class="check">✓</span> ١٠ مشاريع</li>
                    <li><span class="check">✓</span> ٥٠٠ معاملة / شهر</li>
                    <li><span class="check">✓</span> تقارير متقدمة كاملة</li>
                    <li><span class="check">✓</span> تصدير CSV</li>
                    <li><span class="check">✓</span> إشعارات بريد + تطبيق</li>
                    <li><span class="check">✓</span> دعم بالأولوية</li>
                    <li><span class="cross">✕</span> API Access</li>
                </ul>
                @auth
                    <a href="{{ route('billing.index') }}" class="pricing-btn pricing-btn-filled">ترقية للـ Pro</a>
                @else
                    <a href="{{ route('register') }}" class="pricing-btn pricing-btn-filled">ابدأ بـ Pro</a>
                @endauth
            </div>

            <div class="pricing-card">
                <div class="pricing-plan">Business 🚀</div>
                <div class="pricing-price">٢٩٩ <span>ر.س / شهر</span></div>
                <p class="pricing-desc">للشركات والمشاريع الكبيرة</p>
                <ul class="pricing-features">
                    <li><span class="check">✓</span> مشاريع غير محدودة</li>
                    <li><span class="check">✓</span> معاملات غير محدودة</li>
                    <li><span class="check">✓</span> تقارير متقدمة + مخصصة</li>
                    <li><span class="check">✓</span> تصدير CSV + PDF</li>
                    <li><span class="check">✓</span> دعم مخصص ٢٤/٧</li>
                    <li><span class="check">✓</span> API Access كامل</li>
                    <li><span class="check">✓</span> مستخدمون متعددون (قادم)</li>
                </ul>
                @auth
                    <a href="{{ route('billing.index') }}" class="pricing-btn pricing-btn-outline">ترقية للـ Business</a>
                @else
                    <a href="{{ route('register') }}" class="pricing-btn pricing-btn-outline">ابدأ بـ Business</a>
                @endauth
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div>
        <h2>ابدأ اليوم — مجاناً تماماً</h2>
        <p>انضم لأكثر من ١٢٠٠ مستخدم يعرفون وضعهم المالي بدقة كل يوم.</p>
        @auth
            <a href="{{ route('dashboard') }}" class="btn-white">اذهب للوحة التحكم ←</a>
        @else
            <a href="{{ route('register') }}" class="btn-white">أنشئ حسابك المجاني ←</a>
        @endauth
        <p class="cta-note">لا بطاقة ائتمان · إعداد في دقيقة · يمكنك الإلغاء في أي وقت</p>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="#" class="logo" style="color:#fff;text-decoration:none;font-size:1.3rem;">Workuflow<span style="color:#6366f1">.</span></a>
            <p>منصة SaaS مالية للمستقلين وأصحاب الأعمال الصغيرة — وضوح مالي فوري بدون تعقيد.</p>
        </div>
        <div class="footer-col">
            <h4>المنتج</h4>
            <ul>
                <li><a href="#features">المميزات</a></li>
                <li><a href="#pricing">الأسعار</a></li>
                <li><a href="#how">كيف يعمل</a></li>
                <li><a href="{{ route('register') }}">ابدأ مجاناً</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>الحساب</h4>
            <ul>
                <li><a href="{{ route('login') }}">تسجيل الدخول</a></li>
                <li><a href="{{ route('register') }}">إنشاء حساب</a></li>
                <li><a href="{{ route('password.request') }}">نسيت كلمة المرور</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>الدعم</h4>
            <ul>
                <li><a href="#">مركز المساعدة</a></li>
                <li><a href="#">تواصل معنا</a></li>
                <li><a href="#">سياسة الخصوصية</a></li>
                <li><a href="#">شروط الاستخدام</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>© {{ date('Y') }} Workuflow. جميع الحقوق محفوظة.</span>
        <span>مبني بـ ❤️ للمستقلين العرب</span>
    </div>
</footer>

</body>
</html>
