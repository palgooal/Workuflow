<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\Content\PageContentSanitizer;
use App\Support\Enums\PageFooterGroup;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * LegalPagesSeeder — يرحّل محتوى الصفحات القانونية الأربع الحالية
 * (resources/views/legal/{privacy,terms,cookies,data-deletion}.blade.php)
 * إلى جدول pages، ليصبح قابلاً للتعديل من لوحة الأدمن.
 *
 * قابل لإعادة التشغيل بأمان (idempotent): يتخطى أي صفحة slug موجود أصلاً
 * (بما فيها المحذوفة Soft Delete)، فلا يُنشئ نسخاً مكررة أبداً.
 *
 * طريقة الاستخراج: يُصيَّر (render) ملف الـBlade الحقيقي كما يراه الزائر
 * اليوم بالضبط — فيُحل route()/asset()/أي Blade syntax داخله إلى قيمه
 * الفعلية النهائية — ثم يُستخرَج فقط المحتوى الداخلي لعنصر
 * <article class="legal-page-content">، فتكون هذه أعلى ضمانة ممكنة لتطابق
 * 100% مع المحتوى المنشور فعلياً دون أي تغيير في الصياغة القانونية.
 *
 * تُحفَظ أيضاً نسخة احتياطية نصية من كل صفحة رُحِّلت في
 * storage/app/migration-backup/legal-pages-{timestamp}/ قبل أي إدخال
 * لقاعدة البيانات.
 *
 * تشغيل: php artisan db:seed --class=Database\\Seeders\\LegalPagesSeeder
 */
class LegalPagesSeeder extends Seeder
{
    private const LEGAL_PAGES = [
        [
            'slug'              => 'privacy-policy',
            'title'             => 'سياسة الخصوصية',
            'view'              => 'legal.privacy',
            'footer_group'      => 'company',
            'footer_label'      => 'سياسة الخصوصية',
            'sort_order'        => 10,
            'meta_title'        => 'سياسة الخصوصية — دراهم | مال وأعمال',
            'meta_description'  => 'كيف تجمع منصة دراهم | مال وأعمال بياناتك وتستخدمها وتحميها، وحقوقك المتعلقة ببياناتك الشخصية.',
            'document_version'  => '1.1.0',
        ],
        [
            'slug'              => 'terms-of-service',
            'title'             => 'شروط الاستخدام',
            'view'              => 'legal.terms',
            'footer_group'      => 'company',
            'footer_label'      => 'شروط الاستخدام',
            'sort_order'        => 20,
            'meta_title'        => 'شروط الاستخدام — دراهم | مال وأعمال',
            'meta_description'  => 'الشروط والأحكام الحاكمة لاستخدام منصة دراهم | مال وأعمال.',
            'document_version'  => '1.2.0',
        ],
        [
            'slug'              => 'cookie-policy',
            'title'             => 'سياسة الكوكيز',
            'view'              => 'legal.cookies',
            'footer_group'      => 'legal',
            'footer_label'      => 'سياسة الكوكيز',
            'sort_order'        => 10,
            'meta_title'        => 'سياسة الكوكيز — دراهم | مال وأعمال',
            'meta_description'  => 'ملفات تعريف الارتباط (Cookies) المستخدَمة فعلياً على منصة دراهم | مال وأعمال.',
            'document_version'  => '1.1.0',
        ],
        [
            'slug'              => 'data-deletion',
            'title'             => 'سياسة حذف البيانات',
            'view'              => 'legal.data-deletion',
            'footer_group'      => 'legal',
            'footer_label'      => 'سياسة حذف البيانات',
            'sort_order'        => 20,
            'meta_title'        => 'سياسة حذف البيانات — دراهم | مال وأعمال',
            'meta_description'  => 'كيف يمكنك طلب حذف بياناتك من منصة دراهم | مال وأعمال.',
            'document_version'  => '1.1.0',
        ],
    ];

    /** صفحات اختيارية تُنشأ كمسودة فقط — لا تظهر في الفوتر حتى ينشرها الأدمن صراحة */
    private const DRAFT_PAGES = [
        ['slug' => 'about-darahum', 'title' => 'عن دراهم'],
        ['slug' => 'careers',       'title' => 'الوظائف'],
    ];

    public function run(): void
    {
        $backupDir = storage_path('app/migration-backup/legal-pages-' . now()->format('Ymd-His'));
        File::ensureDirectoryExists($backupDir);

        $migrated = [];
        $skipped  = [];
        $failed   = [];

        foreach (self::LEGAL_PAGES as $definition) {
            $result = $this->migrateOne($definition, $backupDir);
            match ($result) {
                'migrated' => $migrated[] = $definition['slug'],
                'skipped'  => $skipped[] = $definition['slug'],
                'failed'   => $failed[] = $definition['slug'],
            };
        }

        foreach (self::DRAFT_PAGES as $draft) {
            $this->createDraftIfMissing($draft['slug'], $draft['title']);
        }

        $this->command?->info('');
        $this->command?->info('── ملخص ترحيل الصفحات القانونية ──');
        $this->command?->info('رُحِّلت: ' . (count($migrated) ? implode(', ', $migrated) : '—'));
        $this->command?->info('تخطّاها (موجودة أصلاً): ' . (count($skipped) ? implode(', ', $skipped) : '—'));
        if (count($failed)) {
            $this->command?->error('فشل استخراج المحتوى: ' . implode(', ', $failed) . ' — راجع يدوياً.');
        }
        $this->command?->info("نسخة احتياطية نصية محفوظة في: {$backupDir}");
    }

    private function migrateOne(array $def, string $backupDir): string
    {
        if (Page::withTrashed()->where('slug', $def['slug'])->exists()) {
            return 'skipped';
        }

        $renderedHtml = view($def['view'])->render();
        $content      = $this->extractArticleContent($renderedHtml);

        if ($content === null) {
            return 'failed';
        }

        // نسخة احتياطية نصية (المحتوى الخام قبل التعقيم) قبل أي إدخال لقاعدة البيانات
        File::put("{$backupDir}/{$def['slug']}.html", $content);

        Page::create([
            'title'             => $def['title'],
            'slug'              => $def['slug'],
            'page_type'         => PageType::Legal,
            'content'           => PageContentSanitizer::clean($content),
            'status'            => PageStatus::Published,
            'show_in_footer'    => true,
            'footer_group'      => $def['footer_group'],
            'footer_label'      => $def['footer_label'],
            'sort_order'        => $def['sort_order'],
            'meta_title'        => $def['meta_title'],
            'meta_description'  => $def['meta_description'],
            'document_version'  => $def['document_version'],
            'published_at'      => now(),
            'last_reviewed_at'  => now(),
        ]);

        return 'migrated';
    }

    /** يستخرج المحتوى الداخلي لعنصر <article class="legal-page-content">...</article> */
    private function extractArticleContent(string $html): ?string
    {
        if (! preg_match('/<article[^>]*class="[^"]*legal-page-content[^"]*"[^>]*>(.*)<\/article>/is', $html, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function createDraftIfMissing(string $slug, string $title): void
    {
        if (Page::withTrashed()->where('slug', $slug)->exists()) {
            return;
        }

        Page::create([
            'title'          => $title,
            'slug'           => $slug,
            'page_type'      => PageType::Marketing,
            'content'        => '<p>محتوى مبدئي — يُرجى تحريره من لوحة الأدمن قبل النشر.</p>',
            'status'         => PageStatus::Draft,
            'show_in_footer' => false,
            'footer_group'   => PageFooterGroup::None,
            'sort_order'     => 0,
        ]);

        $this->command?->info("📝 أُنشئت كمسودة (غير منشورة): {$title} ({$slug})");
    }
}
