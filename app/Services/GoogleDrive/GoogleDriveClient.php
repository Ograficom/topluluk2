<?php

namespace App\Services\GoogleDrive;

use App\Models\GoogleDriveConnection;
use Google\Client;
use Google\Service\Drive;
use RuntimeException;

class GoogleDriveClient
{
    public function make(bool $consent = false): Client
    {
        $client = new Client();
        $client->setApplicationName(config('app.name', 'OgraFi').' Google Drive');
        $client->setClientId((string) config('google-drive.client_id'));
        $client->setClientSecret((string) config('google-drive.client_secret'));
        $client->setRedirectUri((string) config('google-drive.redirect_uri'));
        $client->setScopes(config('google-drive.scopes'));
        $client->setAccessType('offline');
        $client->setPrompt($consent ? 'consent' : 'select_account');

        return $client;
    }

    public function authorizationUrl(): string
    {
        $client = $this->make(true);
        $state = GoogleDriveOAuthState::issue();
        session()->put('google_drive_oauth_state', $state);
        $client->setState($state);

        return $client->createAuthUrl();
    }

    public function client(?GoogleDriveConnection $connection = null): Client
    {
        $connection ??= GoogleDriveConnection::current();

        if (! $connection || ! $connection->isConnected()) {
            throw new RuntimeException('Google Drive hesabı bağlı değil.');
        }

        $client = $this->make();
        $client->setAccessToken($connection->access_token ?? []);

        if ($client->isAccessTokenExpired()) {
            $token = $client->fetchAccessTokenWithRefreshToken($connection->refresh_token);

            if (isset($token['error'])) {
                throw new RuntimeException('Google Drive erişim anahtarı yenilenemedi.');
            }

            if (empty($token['refresh_token'])) {
                $token['refresh_token'] = $connection->refresh_token;
            }

            $connection->access_token = $token;
            $connection->token_expires_at = now()->addSeconds((int) ($token['expires_in'] ?? 3600));
            $connection->save();
        }

        return $client;
    }

    public function service(?GoogleDriveConnection $connection = null): Drive
    {
        return new Drive($this->client($connection));
    }
}
