<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\Post;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('toggle_publish')
                ->label(fn (Post $record) => $record->is_published ? 'Yayından Kaldır' : 'Yayınla')
                ->icon(fn (Post $record) => $record->is_published ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn (Post $record) => $record->is_published ? 'warning' : 'success')
                ->requiresConfirmation()
                ->action(function (Post $record): void {
                    $record->is_published = ! $record->is_published;
                    if ($record->is_published && ! $record->published_at) {
                        $record->published_at = now();
                    }
                    $record->save();
                }),
            Actions\EditAction::make()
                ->label('Düzenle')
                ->icon('heroicon-o-pencil-square'),
        ];
    }
}
