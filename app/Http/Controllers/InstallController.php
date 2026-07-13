<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Support\InstallState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class InstallController extends Controller
{
    public function requirements()
    {
        $requirements = [
            'php' => [
                'label' => 'PHP >= 8.2',
                'passed' => version_compare(PHP_VERSION, '8.2.0', '>='),
            ],
            'ext_pdo' => [
                'label' => 'PDO',
                'passed' => extension_loaded('pdo'),
            ],
            'ext_pdo_mysql' => [
                'label' => 'PDO MySQL',
                'passed' => extension_loaded('pdo_mysql'),
            ],
            'ext_mbstring' => [
                'label' => 'Mbstring',
                'passed' => extension_loaded('mbstring'),
            ],
            'ext_openssl' => [
                'label' => 'OpenSSL',
                'passed' => extension_loaded('openssl'),
            ],
            'ext_xml' => [
                'label' => 'XML',
                'passed' => extension_loaded('xml'),
            ],
            'ext_curl' => [
                'label' => 'cURL',
                'passed' => extension_loaded('curl'),
            ],
            'ext_json' => [
                'label' => 'JSON',
                'passed' => extension_loaded('json'),
            ],
            'ext_fileinfo' => [
                'label' => 'Fileinfo',
                'passed' => extension_loaded('fileinfo'),
            ],
        ];

        $permissions = [
            'storage' => [
                'label' => 'storage/ yazilabilir',
                'passed' => is_writable(storage_path()),
            ],
            'bootstrap_cache' => [
                'label' => 'bootstrap/cache yazilabilir',
                'passed' => is_writable(base_path('bootstrap/cache')),
            ],
            'env' => [
                'label' => '.env yazilabilir',
                'passed' => $this->isEnvWritable(),
            ],
        ];

        $allPassed = collect($requirements)->every(fn ($item) => $item['passed'])
            && collect($permissions)->every(fn ($item) => $item['passed']);

        return view('install.requirements', compact('requirements', 'permissions', 'allPassed'));
    }

    public function database()
    {
        return view('install.database');
    }

    public function saveDatabase(Request $request)
    {
        $data = $request->validate([
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'integer'],
            'db_name' => ['required', 'string'],
            'db_user' => ['required', 'string'],
            'db_pass' => ['nullable', 'string'],
            'app_url' => ['nullable', 'url'],
        ]);

        $this->configureDatabase($data);

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['db_host' => 'Veritabani baglantisi kurulamadi. Bilgileri ve sunucuyu kontrol edin.'])
                ->withInput();
        }

        $this->writeEnv([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'],
            'DB_PORT' => $data['db_port'],
            'DB_DATABASE' => $data['db_name'],
            'DB_USERNAME' => $data['db_user'],
            'DB_PASSWORD' => $data['db_pass'] ?? '',
        ]);

        if (!empty($data['app_url'])) {
            $this->writeEnv(['APP_URL' => $data['app_url']]);
        }

        if (empty(config('app.key'))) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            $this->writeEnv(['APP_KEY' => $key]);
            config(['app.key' => $key]);
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            return back()
                ->withErrors(['db_host' => 'Migrasyonlar basarisiz oldu. Veritabani kullanici izinlerini kontrol edin.'])
                ->withInput();
        }

        return redirect()->route('install.admin');
    }

    public function admin()
    {
        if (!$this->canConnectDatabase()) {
            return redirect()->route('install.database')
                ->withErrors(['db_host' => 'Veritabani baglantisi bulunamadi.']);
        }

        if (User::query()->exists()) {
            $this->markInstalled();
            return redirect()->route('install.finished');
        }

        return view('install.admin');
    }

    public function saveAdmin(Request $request)
    {
        if (!$this->canConnectDatabase()) {
            return redirect()->route('install.database')
                ->withErrors(['db_host' => 'Veritabani baglantisi bulunamadi.']);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
            'role' => User::ROLE_ADMIN,
        ]);

        $team = Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0] . ' Takimi',
            'personal_team' => true,
        ]);

        $user->ownedTeams()->save($team);
        $user->switchTeam($team);

        $this->markInstalled();

        return redirect()->route('install.finished');
    }

    public function finished()
    {
        return view('install.finished');
    }

    private function configureDatabase(array $data): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $data['db_host'],
            'database.connections.mysql.port' => $data['db_port'],
            'database.connections.mysql.database' => $data['db_name'],
            'database.connections.mysql.username' => $data['db_user'],
            'database.connections.mysql.password' => $data['db_pass'] ?? '',
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    private function canConnectDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function isEnvWritable(): bool
    {
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            return is_writable($envPath);
        }

        return is_writable(base_path());
    }

    private function writeEnv(array $values): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            $example = base_path('.env.example');
            if (file_exists($example)) {
                copy($example, $envPath);
            } else {
                file_put_contents($envPath, '');
            }
        }

        $contents = file_get_contents($envPath);
        foreach ($values as $key => $value) {
            $value = $this->escapeEnvValue($value);
            if (preg_match("/^{$key}=.*$/m", $contents)) {
                $contents = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $contents);
            } else {
                $contents .= PHP_EOL . "{$key}={$value}";
            }
        }

        file_put_contents($envPath, trim($contents) . PHP_EOL);
    }

    private function escapeEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"|\'/', $value)) {
            $escaped = str_replace('"', '\"', $value);
            return "\"{$escaped}\"";
        }

        return $value;
    }

    private function markInstalled(): void
    {
        $this->writeEnv(['APP_INSTALLED' => 'true']);
        InstallState::markInstalled();
    }
}
