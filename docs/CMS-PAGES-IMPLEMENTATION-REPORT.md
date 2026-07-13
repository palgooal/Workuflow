# نظام إدارة المحتوى المصغّر (CMS) داخل Filament Admin — التقرير النهائي

**التاريخ:** 2026-07-13
**الحالة:** الكود مكتمل بالكامل، **غير منفَّذ فعلياً بعد** — راجع التنبيه في القسم 0 قبل أي شيء آخر.

---

## 0. تنبيه حرج: بيئة العمل لا تحتوي PHP ولا Composer

بيئة التنفيذ المتاحة لي في هذه الجلسة (sandbox) **لا تحتوي على `php` ولا `composer`** (تحقّقت من ذلك مرتين: `php -v` و`composer --version` أعادا "command not found"). هذا يعني:

- لم أُشغّل `composer require mews/purifier` — فقط أضفتُ السطر يدوياً في `composer.json`.
- لم أُشغّل أي Migration — جداول `pages` و`page_revisions` **غير موجودة فعلياً في قاعدة البيانات بعد**.
- لم أُشغّل `LegalPagesSeeder` — لا يوجد أي محتوى مُرحَّل فعلياً بعد. النظام حالياً يعمل بالكامل عبر السقوط الآمن (fallback) على ملفات Blade الثابتة القديمة، تماماً كما كان قبل هذه الجلسة.
- لم أُشغّل `php artisan test` — جميع الاختبارات مكتوبة ومنطقياً صحيحة حسب مراجعتي اليدوية، لكن **لم يتم تنفيذها فعلياً ولا التأكد من نجاحها**.

**كل الكود في هذا التقرير مكتوب بعناية ومطابق لأنماط المشروع القائمة، لكنه غير مُتحقَّق منه بالتنفيذ.** الخطوات المطلوبة منك لتفعيل النظام موجودة في القسم 9 — يجب تنفيذها وتشغيل الاختبارات قبل اعتبار هذه الميزة جاهزة للإطلاق.

---

## 1. الملفات المضافة والمعدَّلة

### ملفات جديدة

| الملف | الغرض |
|---|---|
| `app/Support/Enums/PageType.php` | Enum: `general` / `marketing` / `legal` |
| `app/Support/Enums/PageStatus.php` | Enum: `draft` / `published` / `archived` |
| `app/Support/Enums/PageFooterGroup.php` | Enum: `product` / `company` / `legal` / `none` |
| `database/migrations/2026_07_13_000001_create_pages_table.php` | جدول `pages` |
| `database/migrations/2026_07_13_000002_create_page_revisions_table.php` | جدول `page_revisions` |
| `app/Models/Page.php` | الموديل الرئيسي + Cache + Versioning + Scopes |
| `app/Models/PageRevision.php` | موديل النسخ التاريخية |
| `app/Policies/PagePolicy.php` | صلاحيات إدارة الصفحات |
| `app/Filament/Resources/PageResource.php` + `Pages/{ListPages,CreatePage,EditPage}.php` | واجهة إدارة الصفحات في Filament |
| `app/Filament/Pages/SiteSettings.php` + `resources/views/filament/pages/site-settings.blade.php` | صفحة إعدادات التواصل ووسائل التواصل الاجتماعي |
| `config/purifier.php` | إعداد HTMLPurifier (preset `page_content`) |
| `app/Support/Content/PageContentSanitizer.php` | تعقيم المحتوى ضد XSS |
| `app/Support/Content/ReservedSlugs.php` | قائمة الـslugs المحجوزة على مستوى النظام |
| `app/Support/Content/TableOfContentsBuilder.php` | استخراج/توليد TOC تلقائياً من عناصر `<h2>` |
| `app/Http/Controllers/PageController.php` | العرض العام (صفحات + قانوني) |
| `resources/views/pages/show.blade.php` | قالب الصفحات العامة (بدون TOC) |
| `resources/views/pages/legal-show.blade.php` | قالب الصفحات القانونية (بنفس تصميم TOC الحالي) |
| `database/seeders/LegalPagesSeeder.php` | ترحيل المحتوى القانوني الحالي (قابل لإعادة التشغيل) |
| `tests/Feature/Pages/*.php` (7 ملفات) | تغطية الاختبارات — القسم 8 |

### ملفات معدَّلة

| الملف | التعديل |
|---|---|
| `composer.json` | إضافة `mews/purifier: ^3.4` |
| `app/Providers/Filament/AdminPanelProvider.php` | إضافة مجموعتَي تنقّل: "المحتوى والصفحات" و"الإعدادات" |
| `app/Providers/AppServiceProvider.php` | تسجيل `PagePolicy`، وإضافة `shareFooterData()` (View Composer للفوتر) |
| `config/services.php` | حذف كتلة `social` القديمة (env-based) — استُبدلت بتعليق يشير إلى `SiteSettings` |
| `routes/web.php` | الروابط القانونية الأربعة أصبحت تستدعي `PageController`؛ إضافة `GET /pages/{slug}` |

**لم يُحذف أي ملف** — لا `docs/legal/*.md` ولا أي Blade قديم، بحسب القيد الصريح في طلبك.

---

## 2. مخطط الجداول (Schema)

### `pages`

مفتاح أساسي **ULID حقيقي** (وليس عمود ulid ثانوي كما في بقية المشروع — هذا انحراف متعمَّد عن نمط `Invoice`/`Quote`، نفّذته حرفياً بناءً على طلبك الصريح "id: ULID"، وأنبّه له هنا للشفافية).

جميع الحقول المطلوبة: `title`, `slug` (unique), `page_type`, `content` (longText), `excerpt`, `status`, `show_in_footer`, `footer_group`, `footer_label`, `sort_order`, `meta_title`, `meta_description`, `og_description`, `document_version`, `published_at`, `last_reviewed_at`, `created_by`/`updated_by` (FK nullable → `users`), timestamps, `softDeletes()`.

**لا يوجد أي DB ENUM** — `page_type`/`status`/`footer_group` كلها `VARCHAR` مع Cast إلى PHP Enum على مستوى الموديل، تماماً كما طلبت.

فهرس مركّب `pages_footer_listing_idx` على `(status, show_in_footer, footer_group, sort_order)` لخدمة استعلام الفوتر بكفاءة، وفهرس على `page_type`.

### `page_revisions`

`id` تلقائي عادي، `page_id` (ULID، FK → `pages.id` مع `cascadeOnDelete`)، `version`, `title`, `content` (longText)، `meta_title`, `meta_description`, `changed_by` (FK nullable → `users`), `change_note`, `published_at`, timestamps. لا `softDeletes` — النسخ التاريخية لا تُحذف أبداً.

---

## 3. آلية إدارة الإعدادات ووسائل التواصل الاجتماعي

صفحة Filament جديدة **"إعدادات الموقع والتواصل"** (`app/Filament/Pages/SiteSettings.php`)، مبنية بنفس نمط `MailSettings`/`PaymentSettings` الحالي تماماً — بدون أي جدول جديد، تُخزَّن في `settings` (`group = 'site'`).

الحقول: `site_contact_email`, `site_contact_phone`, `site_whatsapp_url`, `site_location`, `footer_description` — نص عادي فقط، مع `strip_tags()` دفاعي إضافي عند الحفظ حتى لو تجاوز أحد واجهة الفورم.

المنصات الخمس (X, Facebook, LinkedIn, Instagram, WhatsApp) — لكل منصة حقل `URL` + مفتاح `مفعّلة` (Toggle) منفصلَين. الدالة الساكنة `SiteSettings::activeSocialLinks()` هي المصدر الوحيد الذي تقرأ منه الواجهة العامة، وتُرجِع فقط المنصات التي `enabled = true` **و** لها رابط غير فارغ — أي منصة معطَّلة أو بلا رابط لا تظهر إطلاقاً، ولا استخدام لـ `href="#"` في أي مكان.

`Setting::group()`/`setGroup()` تستخدمان `Cache::rememberForever` وتُفرَّغان تلقائياً عند الحفظ — لا حاجة لأي منطق Cache إضافي.

---

## 4. آلية إدارة الصفحات والفوتر

`PageResource` (Filament Resource كامل) يوفر: عنوان + slug (توليد تلقائي عند الإنشاء + تحقق فريد + رفض الـslugs المحجوزة عبر `ReservedSlugs`)، نوع الصفحة، الحالة، محتوى (Rich Editor بسيط بأزرار محدودة — **بدون Page Builder**)، ملخص، إعدادات الظهور في الفوتر (تبديل + مجموعة + ترتيب + تسمية مخصصة)، حقول SEO، وحقول النسخة القانونية (تظهر فقط لصفحات `legal`).

**الفوتر الديناميكي**: `Page::footerLinks()` تجلب — باستعلام واحد مع Cache لمدة ساعة — كل الصفحات المنشورة الظاهرة في الفوتر، مُجمَّعة حسب `footer_group` ومرتَّبة بـ`sort_order`. تعيين المجموعات على التصميم الحالي: `company` → عمود "الشركة" الحالي، `legal` → شريط الروابط القانونية السفلي الحالي (وليس عموداً جديداً) — هذا يحافظ 100% على الترتيب البصري الحالي مع جعل الأربعة روابط قابلة للتبديل من الأدمن.

`View::composer('layouts.marketing', ...)` في `AppServiceProvider` يمرّر `footerPageLinks`/`footerSocialLinks`/`footerContact` لكل صفحة تستخدم الـlayout، بقيم افتراضية مطابقة تماماً للنصوص المكتوبة يدوياً سابقاً — أي أن الفوتر **لن يتغيّر بصرياً أبداً** حتى يملأ الأدمن الإعدادات فعلياً، ومحاط بـ `try/catch` (نفس نمط `applyMailSettings()` الحالي) بحيث لا ينهار الموقع إن لم تُهاجَر الجداول بعد.

---

## 5. آلية النسخ (Versioning) للصفحات القانونية

عند تعديل صفحة `page_type = legal` **منشورة أصلاً** وإعادة نشرها (`EditPage::mutateFormDataBeforeSave()`):

1. تُحفَظ الحالة **القديمة** (قبل التعديل) كسجل جديد في `page_revisions` عبر `Page::snapshotRevision()`.
2. حقل `change_note` **إلزامي** في الفورم لهذه الحالة تحديداً (`->required()` مشروط).
3. `document_version` يُرفَّع تلقائياً (`nextDocumentVersion()`: مثال `1.0.0 → 1.1.0`).
4. `published_at` و`last_reviewed_at` يُحدَّثان لوقت الحفظ.
5. **لا يُحذف أي سجل نسخة قديمة أبداً** — `page_revisions` لا تملك `softDeletes` أصلاً، فلا مسار لحذفها من الواجهة.

أما تعديل صفحة مسودة أو صفحة غير قانونية، فلا تُطلَب ملاحظة تغيير ولا يُنشأ أي Revision — فقط عند إعادة نشر تعديل على صفحة قانونية **كانت منشورة بالفعل**.

الحذف النهائي (`forceDelete`) لأي صفحة قانونية سبق نشرها **ممنوع بالكامل** على مستوى الـPolicy (`canBeForceDeleted()`) — الخيار الوحيد هو الأرشفة (`status = archived`).

---

## 6. المحتوى المُرحَّل

`LegalPagesSeeder` (قابل لإعادة التشغيل بأمان — يتخطى أي slug موجود مسبقاً، بما فيه المحذوف):

| Slug | العمود/الموضع في الفوتر | إصدار المستند الابتدائي |
|---|---|---|
| `privacy-policy` | "الشركة" | 1.1.0 |
| `terms-of-service` | "الشركة" | 1.2.0 |
| `cookie-policy` | الشريط السفلي | 1.1.0 |
| `data-deletion` | الشريط السفلي | 1.1.0 |

طريقة الاستخراج: تصيير (render) ملف الـBlade الحقيقي كما يراه الزائر فعلياً، ثم استخراج محتوى `<article class="legal-page-content">` فقط — هذا يضمن أن أي `{{ route(...) }}`/`{{ asset(...) }}` داخل الروابط الداخلية للصفحات القانونية محلولة إلى قيم نهائية حقيقية قبل التخزين، وليست نصاً خاماً معطوباً. تُحفَظ أيضاً نسخة نصية احتياطية من كل صفحة رُحِّلت في `storage/app/migration-backup/legal-pages-{تاريخ}/` قبل أي إدخال لقاعدة البيانات.

كما يُنشئ الـSeeder صفحتين إضافيتين **كمسودة فقط** (غير ظاهرتين في الفوتر): "عن دراهم" (`about-darahum`) و"الوظائف" (`careers`) — بحسب طلبك، لا تُنشران تلقائياً.

`docs/legal/*.md` والـBlade الأصلية لم تُمَس ولن تُمَس في هذه المرحلة.

---

## 7. توافق الـRoutes

⚠️ **تصحيح واقعي مهم بخصوص طلبك الأصلي**: الروابط "الحالية" التي ذكرتها (`/privacy-policy`, `/terms-of-service`, ...) **ليست** الروابط الفعلية في المشروع. الروابط الحقيقية القائمة هي:

- `legal.privacy` → `/legal/privacy`
- `legal.terms` → `/legal/terms`
- `legal.cookies` → `/legal/cookies`
- `legal.data-deletion` → `/legal/data-deletion`

هذه الأربعة **بقيت بنفس الرابط واسم الـRoute تماماً بدون أي تغيير**. الفرق الوحيد أن الـclosure الذي كان يعرض `view('legal.privacy')` مباشرة أصبح يستدعي `PageController@legalPrivacy`، والذي بدوره: يبحث عن صفحة مطابقة منشورة في جدول `pages`، فإن وُجدت يعرضها، وإلا **يسقط تلقائياً (fallback)** إلى نفس ملف الـBlade الثابت القديم — بلا أي احتمال انقطاع بغض النظر عن ترتيب تشغيل الـMigration/Seeder.

الروابط القانونية الثلاثة غير المذكورة في طلبك (`legal.refund`, `legal.subscription-terms`, `legal.cancellation`) تركتها كما هي تماماً (Blade ثابت) بناءً على اختيارك الصريح عبر `AskUserQuestion`.

رابط جديد واحد فقط أُضيف: `GET /pages/{slug}` (`pages.show`) — للصفحات العامة/التسويقية الجديدة مستقبلاً. أي صفحة بهذا الرابط من نوع `legal` تُعاد توجيهها (301) تلقائياً إلى رابطها القانوني المخصص لتفادي محتوى مكرر.

---

## 8. الاختبارات

7 ملفات Pest جديدة تحت `tests/Feature/Pages/` (مكتوبة، **غير مُشغَّلة فعلياً** — راجع القسم 0):

| الملف | يغطي |
|---|---|
| `PageContentSanitizerTest.php` | إزالة `<script>`/`<iframe>`/أحداث inline، والإبقاء على العناصر الآمنة |
| `ReservedSlugsTest.php` | رفض الـslugs المحجوزة، وقبول slugs عادية |
| `PagePublicViewTest.php` | مسودة/مؤرشفة/محذوفة = 404، منشورة = 200، slug محجوز = 404 دفاعياً |
| `PageSlugUniquenessTest.php` | رفض تكرار الـslug على مستوى القاعدة |
| `FooterDynamicLinksTest.php` | رابط الفوتر يظهر فقط عند `show_in_footer=true` ومنشورة |
| `SiteSettingsSocialLinksTest.php` | وسائل التواصل غير المفعَّلة أو بلا رابط لا تظهر |
| `PageAuthorizationTest.php` | مستخدم عادي مرفوض عبر Policy و403 على `/admin/pages`؛ `super_admin` مسموح؛ صفحة قانونية منشورة لا يمكن حذفها نهائياً حتى من `super_admin` |
| `PageRevisionVersioningTest.php` | تعديل صفحة قانونية منشورة يحفظ Revision ويرفع `document_version`؛ بلا `change_note` = فشل تحقق؛ تعديل مسودة لا يتطلب شيئاً من ذلك |
| `LegalRoutesCompatibilityTest.php` | الروابط القانونية القديمة تعمل بدون أي صف Page (fallback)؛ الروابط لا تتغيّر؛ الثلاثة خارج النطاق سليمة؛ بعد الترحيل تُعرَض من `pages` بدل fallback؛ `/pages/privacy-policy` يُوجَّه 301 إلى `legal.privacy` |

**النتيجة الفعلية غير معروفة** حتى تُشغِّل `php artisan test --filter=Pages` بنفسك.

---

## 9. خطوات التفعيل المطلوبة منك

بالترتيب:

```
composer install
php artisan migrate
php artisan db:seed --class="Database\Seeders\LegalPagesSeeder"
php artisan test --filter=Pages
php artisan optimize:clear
```

بعدها راجع صفحة "إعدادات الموقع والتواصل" و"الصفحات" داخل `/admin` واملأ بيانات التواصل الحقيقية ووسائل التواصل الاجتماعي الفعلية.

---

## 10. مخاطر وقرارات تحتاج موافقتك

- **لم يُنفَّذ أي كود فعلياً في هذه الجلسة** (القسم 0) — هذا أكبر خطر حالياً. لا تعتبر الميزة جاهزة للإطلاق قبل تنفيذ القسم 9 والتحقق من نجاح كل الاختبارات فعلياً.
- **`Page` يستخدم ULID كمفتاح أساسي حقيقي**، خلافاً لنمط بقية المشروع (`Invoice`/`Quote` تستخدمان عمود `ulid` ثانوي). نُفِّذ بناءً على طلبك الصريح، لكن يستحق الانتباه لو كان قراراً غير مقصود.
- **حذف `docs/legal/*.md` والـBlade القديمة** — لم يحدث ولن يحدث في هذه المرحلة. قرارك صراحة أن يبقى كمرجع محفوظ حتى تتأكد من نجاح الترحيل والتحقق يدوياً من تطابق المحتوى — هذا قرار مستقبلي منفصل يحتاج موافقتك.
- **قالب `pages.show.blade.php` (الصفحات العامة الجديدة) بدون TOC جانبي**، بينما `legal-show.blade.php` يحتفظ بالـTOC الكامل — تفسير معقول لـ"لا Page Builder" لم يُذكر صراحة في طلبك، أذكره هنا للشفافية فقط.
- الاختبارات المتعلقة بـFilament (`PageRevisionVersioningTest`, بعض حالات `PageAuthorizationTest`) تستخدم `Livewire::test()` — وهو المسار القياسي لاختبار صفحات Filament، لكنه لم يُختبَر من قبل في هذا المشروع (لا توجد سابقة)، فقد تحتاج تعديلاً طفيفاً إن ظهر خطأ عند التشغيل الفعلي.
