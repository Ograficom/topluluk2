<?php

namespace App\Filament\Resources\SupportTickets\Schemas;

use App\Models\SupportTicket;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupportTicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Gönderen bilgileri')
                ->schema([
                    TextInput::make('name')->label('Ad')->disabled()->dehydrated(false),
                    TextInput::make('email')->label('E-posta')->disabled()->dehydrated(false),
                    TextInput::make('subject')->label('Konu')->disabled()->dehydrated(false),
                    Select::make('department')
                        ->label('İlgili birim')
                        ->options([
                            'support' => 'Teknik destek',
                            'editorial' => 'Editör ekibi',
                            'partnerships' => 'İş birlikleri',
                            'bug' => 'Hata bildirimi',
                            'suggestion' => 'Görüş ve öneri',
                        ])
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('message')
                        ->label('Mesaj')
                        ->rows(8)
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                    Placeholder::make('attachments_list')
                        ->label('Dosya ekleri')
                        ->content(fn (?SupportTicket $record) => view('filament.support-tickets.attachments', [
                            'ticket' => $record,
                        ]))
                        ->columnSpanFull(),
                ])->columns(2),
            Section::make('Talep yönetimi')
                ->schema([
                    Select::make('status')
                        ->label('Durum')
                        ->options([
                            SupportTicket::STATUS_NEW => 'Yeni',
                            SupportTicket::STATUS_REVIEWING => 'İnceleniyor',
                            SupportTicket::STATUS_REPLIED => 'Yanıtlandı',
                            SupportTicket::STATUS_RESOLVED => 'Çözüldü',
                        ])
                        ->required(),
                    Textarea::make('admin_reply')
                        ->label('Kullanıcıya cevap')
                        ->rows(7)
                        ->helperText('Cevabı kaydettikten sonra üstteki “Cevabı e-posta ile gönder” işlemini kullanın.')
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }
}
