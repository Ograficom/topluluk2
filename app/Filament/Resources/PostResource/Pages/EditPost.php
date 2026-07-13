<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['edited_reason'] = filled(trim((string) ($data['edited_reason'] ?? '')))
            ? trim((string) $data['edited_reason'])
            : null;

        $preview = clone $this->record;
        $preview->fill($data);

        $meaningfulFields = [
            'title',
            'slug',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'category_id',
            'excerpt',
            'featured_image',
            'image_license_url',
            'image_acquire_url',
            'image_credit_text',
            'image_creator_name',
            'image_copyright_notice',
            'content',
            'content_json',
            'is_published',
            'is_pinned',
            'comments_disabled',
            'is_nsfw',
            'published_at',
            'edited_reason',
        ];

        if ($preview->isDirty($meaningfulFields)) {
            $data['edited_at'] = now();
        }

        return $data;
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Sil')
                ->icon('heroicon-o-trash')
                ->color('danger'),
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
