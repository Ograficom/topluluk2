<?php

namespace App\Services;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class AdminNotificationService
{
    public static function sendToAdmins(
        string $title,
        string $body,
        string $url,
        ?int $exceptUserId = null,
    ): void {
        User::query()
            ->where('is_admin', true)
            ->when($exceptUserId !== null, fn ($query) => $query->where('id', '<>', $exceptUserId))
            ->each(function (User $admin) use ($title, $body, $url): void {
                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->actions([
                        Action::make('view')
                            ->label('Görüntüle')
                            ->button()
                            ->url($url)
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($admin, isEventDispatched: true);
            });
    }
}
