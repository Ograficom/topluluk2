<?php

namespace App\Http\Controllers;

use App\Models\PwaSetting;
use Illuminate\Http\JsonResponse;
use App\Services\PwaManifestService;
class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        $pwa = PwaSetting::currentOrNull();
        if (!$pwa || !$pwa->is_enabled) {
            return response()->json(['name' => config('app.name', 'OGrafi')], 404);
        }

        $manifest = app(PwaManifestService::class)->buildManifest($pwa);

        return response()->json($manifest)->header('Content-Type', 'application/manifest+json');
    }

    public function assetLinks(): JsonResponse
    {
        $pwa = PwaSetting::currentOrNull();
        if (!$pwa || !$pwa->twa_enabled || empty($pwa->twa_package_id)) {
            return response()->json([]);
        }

        $fingerprints = array_values(array_filter($pwa->twa_sha256_cert_fingerprints ?? []));
        if (empty($fingerprints)) {
            return response()->json([]);
        }

        return response()->json([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => [
                'namespace' => 'android_app',
                'package_name' => $pwa->twa_package_id,
                'sha256_cert_fingerprints' => $fingerprints,
            ],
        ]]);
    }

}
