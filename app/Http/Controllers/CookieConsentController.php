<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureDeviceIdCookie;
use App\Models\CookieConsent;
use App\Models\CookiePolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CookieConsentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:accept,reject'],
        ]);

        $policy = CookiePolicy::enabled()->latest('updated_at')->first();

        if (!$policy) {
            return response()->json(['message' => 'Çerez politikası aktif değil.'], 404);
        }

        $deviceId = $request->attributes->get('device_id')
            ?? $request->cookie(EnsureDeviceIdCookie::COOKIE_NAME)
            ?? (string) Str::uuid();

        $consent = CookieConsent::updateOrCreate(
            [
                'cookie_policy_id' => $policy->id,
                'device_id' => $deviceId,
                'policy_version' => $policy->version,
            ],
            [
                'user_id' => $request->user()?->id,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr($request->userAgent() ?? '', 0, 500),
                'accepted' => $validated['decision'] === 'accept',
            ]
        );

        return response()->json([
            'status' => $consent->accepted ? 'accepted' : 'rejected',
            'version' => $consent->policy_version,
            'device_id' => $consent->device_id,
        ]);
    }
}
