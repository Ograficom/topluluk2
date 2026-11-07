<?php

namespace App\Filament\Resources\SupportTickets\Pages;

use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Models\SupportTicket;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EditSupportTicket extends EditRecord
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendReply')
                ->label('Cevabı e-posta ile gönder')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Yanıt e-postası gönderilsin mi?')
                ->modalDescription('Kaydedilmiş cevap, talebi gönderen kişinin e-posta adresine iletilecek.')
                ->visible(fn (): bool => filled($this->record->admin_reply))
                ->action(function (): void {
                    /** @var SupportTicket $ticket */
                    $ticket = $this->record;

                    try {
                        Mail::raw($ticket->admin_reply, function ($mail) use ($ticket): void {
                            $mail->to($ticket->email)
                                ->subject('[OgraFi destek #' . $ticket->id . '] ' . $ticket->subject);
                        });

                        $ticket->forceFill([
                            'status' => SupportTicket::STATUS_REPLIED,
                            'replied_at' => now(),
                        ])->save();

                        Notification::make()
                            ->title('Yanıt gönderildi')
                            ->body('Cevap e-posta ile kullanıcıya iletildi.')
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title('E-posta gönderilemedi')
                            ->body('Cevap kaydedildi ancak e-posta gönderiminde hata oluştu.')
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
