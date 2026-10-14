<?php

namespace App\Filament\Resources\PostResource\RelationManagers;

use App\Models\PostContentRevision;
use App\Services\OgrafiContentRevisionService;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContentRevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'contentRevisions';

    protected static ?string $title = 'İçerik sürüm geçmişi';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Başlık')->limit(60)->searchable(),
                TextColumn::make('revision_type')->label('Tür')->badge(),
                TextColumn::make('user.name')->label('Düzenleyen')->placeholder('Sistem'),
                TextColumn::make('revision_reason')->label('Düzenleme nedeni')->limit(70)->placeholder('—'),
                TextColumn::make('created_at')->label('Tarih')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('restore')
                    ->label('Bu sürümü geri yükle')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('İçerik sürümü geri yüklensin mi?')
                    ->modalDescription('Mevcut içerik önce sürüm geçmişine kaydedilir. Bu işlem yalnızca bu gönderinin içeriğini değiştirir.')
                    ->action(function (PostContentRevision $record): void {
                        app(OgrafiContentRevisionService::class)->restore(
                            $this->getOwnerRecord(),
                            $record,
                            'Sürüm geçmişinden geri yüklendi',
                        );
                    }),
            ]);
    }
}
