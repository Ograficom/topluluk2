<?php

namespace App\Http\Controllers;

use App\Models\GoogleDriveConnection;
use App\Services\GoogleDrive\GoogleDriveClient;
use App\Services\GoogleDrive\GoogleDriveOAuthState;
use App\Services\GoogleDrive\GoogleDriveStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class GoogleDriveController extends Controller
{
    public function redirect(GoogleDriveClient $client): RedirectResponse
    {
        abort_unless(auth()->user()?->is_admin === true, 403);
        abort_unless(config('google-drive.client_id') && config('google-drive.client_secret'), 503, 'Google Drive OAuth bilgileri yapılandırılmadı.');

        return redirect()->away($client->authorizationUrl());
    }

    public function callback(Request $request, GoogleDriveClient $driveClient, GoogleDriveStorage $storage): RedirectResponse
    {
        $expectedState = (string) session()->pull('google_drive_oauth_state', '');
        $receivedState = (string) $request->query('state', '');

        abort_unless(GoogleDriveOAuthState::matches($expectedState, $receivedState), 419, 'Google OAuth state doğrulaması başarısız.');

        if ($request->filled('error')) {
            return redirect()->route('filament.admin.pages.google-drive-settings')
                ->withErrors(['drive' => 'Google hesabı bağlantı isteğini reddetti.']);
        }

        $request->validate(['code' => ['required', 'string']]);

        try {
            $client = $driveClient->make(true);
            $token = $client->fetchAccessTokenWithAuthCode($request->string('code')->toString());

            if (isset($token['error'])) {
                throw new \RuntimeException('OAuth token alınamadı.');
            }

            $client->setAccessToken($token);
            $user = (new \Google\Service\Oauth2($client))->userinfo->get();
            $connection = GoogleDriveConnection::current() ?? new GoogleDriveConnection();
            $connection->account_email = $user->email;
            $connection->access_token = $token;

            if (! empty($token['refresh_token'])) {
                $connection->refresh_token = $token['refresh_token'];
            }

            $connection->token_expires_at = now()->addSeconds((int) ($token['expires_in'] ?? 3600));
            $connection->scopes = config('google-drive.scopes');
            $connection->connected_at = now();
            $connection->last_error = null;
            $connection->save();

            $storage->ensureRootFolder();

            return redirect()->route('filament.admin.pages.google-drive-settings')
                ->with('status', 'Google Drive hesabı bağlandı.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('filament.admin.pages.google-drive-settings')->withErrors([
                'drive' => 'Google Drive bağlantısı kurulamadı. OAuth ayarlarını ve izinleri kontrol edin.',
            ]);
        }
    }

    public function disconnect(): RedirectResponse
    {
        abort_unless(auth()->user()?->is_admin === true, 403);
        GoogleDriveConnection::query()->delete();

        return redirect()->route('filament.admin.pages.google-drive-settings')
            ->with('status', 'Google Drive bağlantısı kaldırıldı.');
    }
}
