<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class FigmaWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse|Response
    {
        $expectedPasscode = (string) config('figma.webhook_passcode');
        $receivedPasscode = (string) $request->input('passcode', '');

        if ($expectedPasscode === '' || ! hash_equals($expectedPasscode, $receivedPasscode)) {
            return response()->json(['message' => 'Invalid webhook passcode.'], 400);
        }

        $eventType = (string) $request->input('event_type', '');

        if ($eventType === 'PING') {
            return response()->noContent();
        }

        if (! in_array($eventType, ['FILE_UPDATE', 'FILE_VERSION_UPDATE'], true)) {
            return response()->json(['message' => 'Event ignored.'], 202);
        }

        $configuredFileKey = (string) config('figma.file_key');
        $receivedFileKey = (string) $request->input('file_key', '');

        if ($configuredFileKey === '' || ! hash_equals($configuredFileKey, $receivedFileKey)) {
            return response()->json(['message' => 'Unexpected Figma file.'], 400);
        }

        $repository = (string) config('figma.github.repository');
        $githubToken = (string) config('figma.github.token');
        $dispatchEvent = (string) config('figma.github.event_type', 'figma_update');

        if ($repository === '' || $githubToken === '') {
            return response()->json(['message' => 'GitHub dispatch is not configured.'], 503);
        }

        $response = Http::withToken($githubToken)
            ->acceptJson()
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->post("https://api.github.com/repos/{$repository}/dispatches", [
                'event_type' => $dispatchEvent,
                'client_payload' => [
                    'figma_event_type' => $eventType,
                    'file_key' => $receivedFileKey,
                    'file_name' => (string) $request->input('file_name', ''),
                    'version_id' => (string) $request->input('version_id', ''),
                    'timestamp' => (string) $request->input('timestamp', now()->toIso8601String()),
                ],
            ]);

        if (! $response->successful()) {
            report(new \RuntimeException('Figma webhook could not dispatch GitHub sync: '.$response->body()));

            return response()->json(['message' => 'GitHub dispatch failed.'], 502);
        }

        return response()->json(['message' => 'Figma sync dispatched.'], 202);
    }
}
