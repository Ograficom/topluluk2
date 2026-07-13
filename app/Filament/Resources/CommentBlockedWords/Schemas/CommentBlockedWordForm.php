<?php

namespace App\Filament\Resources\CommentBlockedWords\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CommentBlockedWordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('word')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
