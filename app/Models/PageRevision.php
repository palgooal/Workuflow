<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PageRevision — لقطة من نسخة منشورة سابقاً لصفحة (خصوصاً القانونية)،
 * تُحفظ تلقائياً قبل أي إعادة نشر لتعديل على صفحة قانونية منشورة.
 *
 * سجل تاريخي فقط — لا يُحذف، ولا تُبنى عليه أي عملية استرجاع تلقائي.
 */
class PageRevision extends Model
{
    protected $fillable = [
        'page_id',
        'version',
        'title',
        'content',
        'meta_title',
        'meta_description',
        'changed_by',
        'change_note',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
