<?php

namespace App\Support\Content;

use DOMDocument;
use Illuminate\Support\Str;

/**
 * يبني جدول محتويات (TOC) تلقائياً من عناصر <h2> الموجودة داخل محتوى
 * صفحة (Page::$content)، ويضيف id فريد لكل عنوان إن لم يكن موجوداً أصلاً.
 *
 * هذا ليس Page Builder — فقط قراءة/تطعيم بسيط لمحتوى HTML مُعقَّم أصلاً
 * (عبر PageContentSanitizer)، يُستخدَم لعرض شريط تنقّل جانبي (كما في
 * الصفحات القانونية الأربع الحالية) دون الحاجة لتخزين بنية TOC يدوياً
 * في قاعدة البيانات.
 */
class TableOfContentsBuilder
{
    /**
     * @return array{html: string, toc: array<int, array{id: string, label: string}>}
     */
    public static function build(string $html): array
    {
        if (trim($html) === '') {
            return ['html' => $html, 'toc' => []];
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $html . '</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $root = $dom->getElementsByTagName('div')->item(0);

        if (! $root) {
            return ['html' => $html, 'toc' => []];
        }

        $toc  = [];
        $used = [];

        foreach ($root->getElementsByTagName('h2') as $heading) {
            $text = trim($heading->textContent);

            if ($text === '') {
                continue;
            }

            $id = trim($heading->getAttribute('id'));

            if ($id === '') {
                $id = Str::slug($text) ?: 'section';
            }

            $base = $id;
            $i    = 2;

            while (in_array($id, $used, true)) {
                $id = "{$base}-{$i}";
                $i++;
            }

            $used[] = $id;
            $heading->setAttribute('id', $id);

            $toc[] = ['id' => $id, 'label' => $text];
        }

        $innerHtml = '';
        foreach ($root->childNodes as $child) {
            $innerHtml .= $dom->saveHTML($child);
        }

        return ['html' => $innerHtml, 'toc' => $toc];
    }
}
