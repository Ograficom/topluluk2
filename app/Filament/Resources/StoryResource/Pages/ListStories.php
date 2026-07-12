<?php

namespace App\Filament\Resources\StoryResource\Pages;

use App\Filament\Resources\StoryResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListStories extends ListRecords
{
    protected static string $resource = StoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            __('All') => Tab::make(),
            __('Approved') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('approved_at')),
            __('Published') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('published_at')),
            __('Draft') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('published_at')),
            __('Featured') => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('featured', true)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return __('All');
    }
}
