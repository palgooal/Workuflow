<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Support\Content\PageContentSanitizer;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * منطق Versioning: عند إعادة نشر تعديل على صفحة قانونية منشورة أصلاً —
     * تُحفَظ النسخة القديمة في page_revisions قبل الكتابة فوقها، ويُطلَب
     * change_note (تحقّق عبر required() في الفورم أصلاً)، ويُحدَّث
     * document_version وpublished_at وlast_reviewed_at تلقائياً.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var \App\Models\Page $record */
        $record = $this->record;

        // تعقيم المحتوى ضد XSS قبل أي حفظ (لا نثق بالمحرر وحده)
        $data['content'] = PageContentSanitizer::clean($data['content'] ?? '');

        if ($record->requiresChangeNote()) {
            $changeNote = $data['change_note'] ?? null;

            // لقطة من الحالة الحالية (قبل التعديل) — record لا يزال بقيمه الأصلية هنا
            $record->snapshotRevision($changeNote, auth()->id());

            $data['document_version'] = $record->nextDocumentVersion();
            $data['published_at']     = now();
            $data['last_reviewed_at'] = now();
        } elseif (($data['status'] ?? null) === PageStatus::Published->value && $record->status !== PageStatus::Published) {
            // أول نشر لصفحة كانت مسودة
            $data['published_at']     = now();
            $data['last_reviewed_at'] = now();

            if ($record->page_type === PageType::Legal && empty($data['document_version'])) {
                $data['document_version'] = $record->document_version ?: '1.0.0';
            }
        }

        unset($data['change_note']);

        return $data;
    }
}
