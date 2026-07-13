<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use App\Support\Content\PageContentSanitizer;
use App\Support\Enums\PageStatus;
use App\Support\Enums\PageType;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // تعقيم المحتوى ضد XSS قبل أي حفظ (لا نثق بالمحرر وحده)
        $data['content'] = PageContentSanitizer::clean($data['content'] ?? '');

        $isPublished = ($data['status'] ?? null) === PageStatus::Published->value;

        if ($isPublished) {
            $data['published_at']     = now();
            $data['last_reviewed_at'] = now();

            if (($data['page_type'] ?? null) === PageType::Legal->value && empty($data['document_version'])) {
                $data['document_version'] = '1.0.0';
            }
        }

        return $data;
    }
}
