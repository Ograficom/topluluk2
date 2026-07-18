<?php

namespace App\View\Components;

use App\Http\Middleware\EnsureDeviceIdCookie;
use App\Models\CookieConsent;
use App\Models\CookiePolicy;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CookieBanner extends Component
{
    public function render(): View|Closure|string
    {
        $routeName = request()->route()?->getName() ?? '';
        $path = ltrim((string) request()->path(), '/');
        $isFilament = ($routeName !== '' && str_starts_with($routeName, 'filament.'))
            || str_starts_with($path, 'admin')
            || str_starts_with($path, 'filament');

        if ($isFilament) {
            return '';
        }

        $policy = CookiePolicy::enabled()->latest('updated_at')->first();

        if (!$policy) {
            return '';
        }

        $deviceId = request()->attributes->get('device_id') ?? request()->cookie(EnsureDeviceIdCookie::COOKIE_NAME);
        $ipAddress = request()->ip();

        $hasConsent = CookieConsent::query()
            ->where('cookie_policy_id', $policy->id)
            ->where('policy_version', $policy->version)
            ->where(function ($query) use ($deviceId, $ipAddress) {
                if ($deviceId) {
                    $query->orWhere('device_id', $deviceId);
                }

                if ($ipAddress) {
                    $query->orWhere('ip_address', $ipAddress);
                }
            })
            ->exists();

        return view('components.cookie-banner', [
            'policy' => $policy,
            'showBanner' => !$hasConsent,
        ]);
    }
}
