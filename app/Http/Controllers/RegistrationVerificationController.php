<?php

namespace App\Http\Controllers;

use App\Models\PendingRegistration;
use App\Models\Team;
use App\Models\User;
use App\Notifications\PendingRegistrationCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegistrationVerificationController extends Controller
{
    public function requestCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:users,email'],
        ], [
            'email.unique' => 'Bu e-posta adresiyle daha önce üyelik oluşturulmuş.',
        ]);

        PendingRegistration::query()->where('created_at', '<', now()->subDay())->delete();

        $code = (string) random_int(100000, 999999);
        $pending = PendingRegistration::query()->updateOrCreate(
            ['email' => Str::lower($validated['email'])],
            [
                'token' => Str::random(64),
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
                'verified_at' => null,
                'sent_at' => now(),
            ],
        );

        $request->session()->put('pending_registration_token', $pending->token);
        Notification::route('mail', $pending->email)
            ->notify(new PendingRegistrationCodeNotification($code));

        return redirect()->route('register.verify');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        $pending = $this->pending($request);

        if (!$pending) {
            return redirect()->route('register');
        }

        return view('auth.registration-flow', ['stage' => 'verify', 'pending' => $pending]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $pending = $this->pending($request);

        if (!$pending || $pending->expires_at->isPast()) {
            throw ValidationException::withMessages(['code' => 'Kodun süresi dolmuş. E-postanızı yeniden girin.']);
        }

        if ($pending->attempts >= 5) {
            throw ValidationException::withMessages(['code' => 'Çok fazla hatalı deneme yapıldı. Yeni kod isteyin.']);
        }

        if (!Hash::check($validated['code'], $pending->code_hash)) {
            $pending->increment('attempts');
            throw ValidationException::withMessages(['code' => 'Girdiğiniz doğrulama kodu hatalı.']);
        }

        $pending->forceFill(['verified_at' => now()])->save();

        return redirect()->route('register.complete');
    }

    public function showComplete(Request $request): View|RedirectResponse
    {
        $pending = $this->pending($request);

        if (!$pending?->verified_at) {
            return redirect()->route($pending ? 'register.verify' : 'register');
        }

        return view('auth.registration-flow', ['stage' => 'complete', 'pending' => $pending]);
    }

    public function complete(Request $request): RedirectResponse
    {
        $pending = $this->pending($request);
        if (!$pending?->verified_at) {
            return redirect()->route('register');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($pending, $validated): User {
            if (User::query()->where('email', $pending->email)->exists()) {
                throw ValidationException::withMessages(['email' => 'Bu e-posta adresi artık kullanımda.']);
            }

            $isFirstUser = !User::query()->exists();
            $user = User::create([
                'name' => $validated['name'],
                'email' => $pending->email,
                'password' => Hash::make($validated['password']),
                'role' => $isFirstUser ? User::ROLE_ADMIN : User::ROLE_WRITER,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            $user->ownedTeams()->save(Team::forceCreate([
                'user_id' => $user->id,
                'name' => explode(' ', $user->name, 2)[0].' Takımı',
                'personal_team' => true,
            ]));

            $pending->delete();

            return $user;
        });

        Auth::login($user);
        $request->session()->forget('pending_registration_token');
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Üyeliğiniz oluşturuldu.');
    }

    private function pending(Request $request): ?PendingRegistration
    {
        $token = (string) $request->session()->get('pending_registration_token', '');

        return $token === '' ? null : PendingRegistration::query()->where('token', $token)->first();
    }
}
