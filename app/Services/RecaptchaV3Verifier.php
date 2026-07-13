<?php

namespace App\Services;

use App\Models\RecaptchaSetting;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class RecaptchaV3Verifier
{
    public function verify(string $token, string $action, ?string $ip = null): array
    {
        $settings = RecaptchaSetting::current();
        $secret = $settings->resolvedSecretKey();

        if (!$settings->isReady() || empty($secret)) {
            return [
                'success' => false,
                'score' => 0.0,
                'error' => 'recaptcha_not_configured',
            ];
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://www.google.com/recaptcha/api/siteverify', array_filter([
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $ip,
                ]))
                ->throw();
        } catch (RequestException $e) {
            return [
                'success' => false,
                'score' => 0.0,
                'error' => 'recaptcha_request_failed',
            ];
        }

        $payload = (array) $response->json();

        $success = (bool) ($payload['success'] ?? false);
        $score = (float) ($payload['score'] ?? 0.0);
        $responseAction = (string) ($payload['action'] ?? '');
        $hostname = (string) ($payload['hostname'] ?? '');

        if (!$success) {
            return [
                'success' => false,
                'score' => $score,
                'error' => 'recaptcha_failed',
                'details' => $payload['error-codes'] ?? null,
            ];
        }

        if ($settings->verify_action && $responseAction !== $action) {
            return [
                'success' => false,
                'score' => $score,
                'error' => 'recaptcha_action_mismatch',
            ];
        }

        if ($score < (float) $settings->minimum_score) {
            return [
                'success' => false,
                'score' => $score,
                'error' => 'recaptcha_low_score',
            ];
        }

        $allowedHostnames = $settings->allowedHostnamesList();
        if (!empty($allowedHostnames) && $hostname !== '' && !in_array($hostname, $allowedHostnames, true)) {
            return [
                'success' => false,
                'score' => $score,
                'error' => 'recaptcha_hostname_not_allowed',
            ];
        }

        return [
            'success' => true,
            'score' => $score,
            'hostname' => $hostname,
            'action' => $responseAction,
        ];
    }
}

