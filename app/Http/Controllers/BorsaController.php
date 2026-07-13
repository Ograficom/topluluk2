<?php

namespace App\Http\Controllers;

use App\Services\BorsaService;
use Illuminate\Http\JsonResponse;

class BorsaController extends Controller
{
    public function ticker(BorsaService $borsaService): JsonResponse
    {
        $ticker = $borsaService->ticker();
        $rates = [];

        if (is_string($ticker)) {
            if (preg_match('/USD\\/TRY\\s+([0-9.,]+)/i', $ticker, $matches)) {
                $rates['usdtry'] = $this->normalizeRate($matches[1]);
            }

            if (preg_match('/EUR\\/TRY\\s+([0-9.,]+)/i', $ticker, $matches)) {
                $rates['eurtry'] = $this->normalizeRate($matches[1]);
            }

            $rates = array_filter($rates, static fn ($value) => is_float($value));
        }

        return response()->json([
            'ticker' => $ticker,
            'rates' => $rates,
        ]);
    }

    private function normalizeRate(string $value): ?float
    {
        $value = trim($value);
        $value = str_contains($value, ',')
            ? str_replace(['.', ','], ['', '.'], $value)
            : $value;
        $rate = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $rate !== false && $rate > 0 ? (float) $rate : null;
    }
}
