<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\Content\ReservedSlugs;
use App\Support\Content\TableOfContentsBuilder;
use App\Support\Enums\PageType;

/**
 * PageController — العرض العام لصفحات نظام إدارة المحتوى المصغّر.
 *
 * - show()      : GET /pages/{slug} — أي صفحة عامة/تسويقية منشورة جديدة
 *                 (مثل "عن دراهم" أو "الوظائف" بعد نشرها من الأدمن).
 * - legalX()    : الروابط القانونية الأربع الحالية — الرابط واسم الـRoute
 *                 يبقيان كما هما تماماً (لا تغيير)، لكن المحتوى يُقرأ الآن
 *                 من جدول pages إن كانت الصفحة مُرحَّلة ومنشورة، مع سقوط
 *                 آمن (fallback) على ملف الـBlade الثابت الحالي إن لم تكن
 *                 كذلك بعد — لضمان استمرار عمل هذه الصفحات في كل الأحوال.
 */
class PageController extends Controller
{
    public function show(string $slug)
    {
        if (ReservedSlugs::isReserved($slug)) {
            abort(404);
        }

        $page = Page::findPublishedBySlug($slug);

        if (! $page) {
            abort(404);
        }

        // الصفحات القانونية لها روابط رسمية مخصصة — تُوجَّه إليها لتفادي
        // محتوى مكرر (Duplicate Content) بدل عرضها أيضاً هنا.
        if ($page->page_type === PageType::Legal) {
            $canonical = match ($page->slug) {
                'privacy-policy'   => 'legal.privacy',
                'terms-of-service' => 'legal.terms',
                'cookie-policy'    => 'legal.cookies',
                'data-deletion'    => 'legal.data-deletion',
                default            => null,
            };

            if ($canonical) {
                return redirect()->route($canonical, [], 301);
            }
        }

        $built = TableOfContentsBuilder::build($page->content);

        return view('pages.show', [
            'page' => $page,
            'toc'  => $built['toc'],
            'html' => $built['html'],
        ]);
    }

    public function legalPrivacy()
    {
        return $this->renderLegal('privacy-policy', 'legal.privacy', 'legal.privacy');
    }

    public function legalTerms()
    {
        return $this->renderLegal('terms-of-service', 'legal.terms', 'legal.terms');
    }

    public function legalCookies()
    {
        return $this->renderLegal('cookie-policy', 'legal.cookies', 'legal.cookies');
    }

    public function legalDataDeletion()
    {
        return $this->renderLegal('data-deletion', 'legal.data-deletion', 'legal.data-deletion');
    }

    private function renderLegal(string $internalSlug, string $fallbackView, string $routeName)
    {
        $page = Page::findPublishedBySlug($internalSlug);

        if (! $page) {
            // لم تُرحَّل بعد (أو تعطّلت) — نعرض الملف الثابت الحالي كما هو
            return view($fallbackView);
        }

        $built = TableOfContentsBuilder::build($page->content);

        return view('pages.legal-show', [
            'page'      => $page,
            'toc'       => $built['toc'],
            'html'      => $built['html'],
            'routeName' => $routeName,
        ]);
    }
}
