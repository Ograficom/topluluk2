<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Services\AI\PostAiAssistantService;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
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
        return [
            Actions\Action::make('openai_edit')
                ->label('AI ile düzenle')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalHeading('OpenAI ile gönderiyi düzenle')
                ->modalDescription('İşlem uygulanınca gönderi kaydedilir. Başlık/SEO işlemleri içeriğe dokunmaz. İçerik işlemlerinde medya veya özel EditorJS blokları varsa güvenlik için işlem durdurulur.')
                ->modalSubmitActionLabel('Uygula')
                ->schema([
                    Select::make('operation')
                        ->label('İşlem')
                        ->options([
                            'rewrite' => 'Gönderiyi yeniden yaz',
                            'proofread' => 'Yazım ve anlatımı düzelt',
                            'shorten' => 'Kısalt',
                            'expand' => 'Mevcut bilgilerle genişlet',
                            'title' => 'Başlığı iyileştir',
                            'seo' => 'SEO alanlarını üret / iyileştir',
                            'custom' => 'Özel talimat',
                        ])
                        ->default('rewrite')
                        ->required(),
                    Select::make('model')
                        ->label('OpenAI modeli')
                        ->options([
                            'gpt-5.6-luna' => 'GPT-5.6 Luna — en ekonomik',
                            'gpt-5.6-terra' => 'GPT-5.6 Terra — dengeli',
                            'gpt-5.6-sol' => 'GPT-5.6 Sol — güçlü',
                            'gpt-6-astra' => 'GPT-6 Astra — en güçlü / pahalı',
                        ])
                        ->default(fn (): string => (string) config('services.openai.model', 'gpt-5.6-luna'))
                        ->required(),
                    Textarea::make('instruction')
                        ->label('Ek talimat')
                        ->rows(4)
                        ->maxLength(2000)
                        ->helperText('Özel talimat işleminde zorunludur. Diğer işlemlerde istersen ek yönlendirme yazabilirsin.'),
                ])
                ->action(function (array $data): void {
                    try {
                        $result = app(PostAiAssistantService::class)->editAndSave(
                            post: $this->record,
                            operation: (string) $data['operation'],
                            instruction: $data['instruction'] ?? null,
                            model: $data['model'] ?? null,
                        );

                        Notification::make()
                            ->title('AI düzenlemesi uygulandı')
                            ->body((string) ($result['change_summary'] ?? 'Gönderi güncellendi.'))
                            ->success()
                            ->send();

                        $this->redirect(PostResource::getUrl('edit', ['record' => $this->record]));
                    } catch (\Throwable $e) {
                        report($e);

                        Notification::make()
                            ->title('AI düzenlemesi başarısız')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
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
