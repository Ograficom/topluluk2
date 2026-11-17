<?php
namespace App\Http\Controllers;
use App\Models\GoogleDriveConnection;
use App\Services\GoogleDrive\GoogleDriveClient;
use App\Services\GoogleDrive\GoogleDriveStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;
class GoogleDriveController extends Controller
{
    public function index(): View\n    {\n        abort_unless(auth()->user()?->is_admin === true, 403);\n        return view('filament.pages.google-drive-settings',['connection'=>GoogleDriveConnection::current()]);\n    }
    public function redirect(GoogleDriveClient $client): RedirectResponse
    {
        abort_unless(config('google-drive.client_id') && config('google-drive.client_secret'),503,'Google Drive OAuth bilgileri yapılandırılmadı.');
        return redirect()->away($client->authorizationUrl());
    }
    public function callback(Request $request, GoogleDriveClient $driveClient, GoogleDriveStorage $storage): RedirectResponse
    {
        $request->validate(['code'=>['required','string']]);
        try {
            $client=$driveClient->make(true);
            $token=$client->fetchAccessTokenWithAuthCode($request->string('code')->toString());
            if (isset($token['error'])) throw new \RuntimeException('OAuth token alınamadı.');
            $client->setAccessToken($token);
            $user=(new \Google\Service\Oauth2($client))->userinfo->get();
            $connection=GoogleDriveConnection::current() ?? new GoogleDriveConnection();
            $connection->account_email=$user->email;
            $connection->access_token=$token;
            if (!empty($token['refresh_token'])) $connection->refresh_token=$token['refresh_token'];
            $connection->token_expires_at=now()->addSeconds((int)($token['expires_in'] ?? 3600));
            $connection->scopes=config('google-drive.scopes');
            $connection->connected_at=now();
            $connection->last_error=null;
            $connection->save();
            $storage->ensureRootFolder();
            return redirect()->route('admin.google-drive.index')->with('status','Google Drive hesabı bağlandı.');
        } catch (Throwable $e) {
            report($e);
            return redirect()->route('admin.google-drive.index')->withErrors(['drive'=>'Google Drive bağlantısı kurulamadı. OAuth ayarlarını kontrol edin.']);
        }
    }
    public function disconnect(): RedirectResponse
    {
        GoogleDriveConnection::query()->delete();
        return redirect()->route('admin.google-drive.index')->with('status','Google Drive bağlantısı kaldırıldı.');
    }
}