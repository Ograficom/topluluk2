<?php

namespace App\Http\Controllers;

use App\Services\BorsaService;
use Illuminate\Http\JsonResponse;

class BorsaController extends Controller
{
    public function ticker(BorsaService $borsaService): JsonResponse
    {
        $ticker = $borsaService->ticker();

        return response()->json([
            'ticker' => $ticker,
        ]);
    }
}
