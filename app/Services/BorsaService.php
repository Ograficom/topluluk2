<?php

namespace App\Services;

use App\Models\BorsaSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BorsaService
{
    public function ticker(): ?string
    {
        $setting = BorsaSetting::currentOrNull();
        if (!$setting || !$setting->is_active) {
            return null;
        }

        $symbols = $this->normalizeSymbols($setting->symbols);
        $pairs = $this->normalizePairs($setting->pair_symbols);
        $hasSymbolPlaceholder = $this->hasSymbolPlaceholder($setting->api_url);
        if ($hasSymbolPlaceholder && $symbols === []) {
            return null;
        }
        if ($symbols === [] && $pairs === []) {
            return null;
        }

        $url = $this->buildUrl($setting, $symbols);
        if (!$url) {
            return null;
        }

        $cacheKey = 'borsa.ticker';
        $ttl = max(10, (int) ($setting->cache_seconds ?? 60));

        return Cache::remember($cacheKey, $ttl, function () use ($url, $setting, $symbols, $pairs) {
            try {
                $response = Http::timeout(3)->get($url);
            } catch (\Throwable $e) {
                return null;
            }

            if (!$response->ok()) {
                return null;
            }

            $payload = $response->json();
            if (!is_array($payload)) {
                return null;
            }

            $items = $setting->response_path
                ? data_get($payload, $setting->response_path)
                : $payload;

            if (!is_array($items)) {
                return null;
            }

            $pairs = $this->normalizePairs($setting->pair_symbols);
            $symbolKey = trim((string) ($setting->symbol_key ?? 'symbol'));
            $priceKey = trim((string) ($setting->price_key ?? 'price'));
            $changeKey = trim((string) ($setting->change_key ?? 'change'));

            $parts = [];

            $rateMap = $this->buildRateMap($items, $symbolKey, $priceKey, $changeKey);
            if (!empty($pairs)) {
                $parts = $this->formatPairs($pairs, $rateMap, (string) ($setting->base_symbol ?? ''));
            } else {
                $limit = count($symbols);
                $count = 0;
                foreach ($rateMap as $symbol => $row) {
                    $parts[] = $this->formatItem($symbol, $row['price'], $row['change']);
                    $count++;
                    if ($count >= $limit) {
                        break;
                    }
                }
            }

            if ($parts === []) {
                return null;
            }

            return implode(' | ', $parts);
        });
    }

    private function normalizeSymbols(?string $symbols): array
    {
        $raw = trim((string) $symbols);
        if ($raw === '') {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn ($sym) => trim($sym))
            ->filter()
            ->values()
            ->all();
    }

    private function normalizePairs(?string $pairs): array
    {
        $raw = trim((string) $pairs);
        if ($raw === '') {
            return [];
        }

        return collect(explode(',', $raw))
            ->map(fn ($pair) => strtoupper(trim($pair)))
            ->filter(fn ($pair) => str_contains($pair, '/'))
            ->values()
            ->all();
    }

    private function hasSymbolPlaceholder(?string $template): bool
    {
        $template = (string) $template;
        return str_contains($template, '{symbols}')
            || str_contains($template, '{symbols_csv}')
            || str_contains($template, '{symbols_json}');
    }

    private function buildUrl(BorsaSetting $setting, array $symbols): ?string
    {
        $template = trim((string) ($setting->api_url ?? ''));
        if ($template === '') {
            return null;
        }

        $csv = implode(',', $symbols);
        $json = json_encode($symbols, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $apiKey = (string) ($setting->api_key ?? '');

        $url = str_replace(
            ['{symbols}', '{symbols_csv}', '{symbols_json}', '{apikey}'],
            [$csv, $csv, $json, $apiKey],
            $template
        );

        return $url;
    }

    private function buildRateMap(array $items, string $symbolKey, string $priceKey, string $changeKey): array
    {
        $map = [];

        foreach ($items as $key => $item) {
            if (is_array($item)) {
                $symbol = $symbolKey !== '' ? data_get($item, $symbolKey) : null;
                if (!is_string($symbol) || $symbol === '') {
                    $symbol = is_string($key) ? $key : null;
                }
                $price = $priceKey !== '' ? data_get($item, $priceKey) : null;
                if ($symbol === null || $price === null) {
                    continue;
                }
                $change = $changeKey !== '' ? data_get($item, $changeKey) : null;
                $map[$symbol] = [
                    'price' => $price,
                    'change' => $change,
                ];
                continue;
            }

            if (is_string($key)) {
                $map[$key] = [
                    'price' => $item,
                    'change' => null,
                ];
            }
        }

        return $map;
    }

    private function formatPairs(array $pairs, array $rateMap, string $baseSymbol): array
    {
        $baseSymbol = strtoupper(trim($baseSymbol));
        $parts = [];

        foreach ($pairs as $pair) {
            [$from, $to] = array_pad(explode('/', $pair, 2), 2, null);
            $from = strtoupper(trim((string) $from));
            $to = strtoupper(trim((string) $to));

            if ($from === '' || $to === '') {
                continue;
            }

            $price = $this->computePairPrice($from, $to, $rateMap, $baseSymbol);
            if ($price === null) {
                continue;
            }

            $parts[] = $from . '/' . $to . ' ' . $this->formatNumber($price);
        }

        return $parts;
    }

    private function computePairPrice(string $from, string $to, array $rateMap, string $baseSymbol): ?float
    {
        $baseSymbol = $baseSymbol !== '' ? $baseSymbol : 'USD';

        if ($from === $to) {
            return 1.0;
        }

        if ($from === $baseSymbol) {
            return $this->getRateValue($rateMap, $to);
        }

        if ($to === $baseSymbol) {
            $rate = $this->getRateValue($rateMap, $from);
            return $rate && $rate != 0.0 ? (1 / $rate) : null;
        }

        $rateTo = $this->getRateValue($rateMap, $to);
        $rateFrom = $this->getRateValue($rateMap, $from);
        if ($rateTo === null || $rateFrom === null || $rateFrom == 0.0) {
            return null;
        }

        return $rateTo / $rateFrom;
    }

    private function getRateValue(array $rateMap, string $symbol): ?float
    {
        if (!array_key_exists($symbol, $rateMap)) {
            return null;
        }

        $price = $rateMap[$symbol]['price'] ?? null;
        if ($price === null || !is_numeric($price)) {
            return null;
        }

        return (float) $price;
    }

    private function formatItem(string $symbol, mixed $price, mixed $change): string
    {
        $priceText = $this->formatNumber($price);
        $changeText = $change !== null ? $this->formatNumber($change) : null;

        $label = Str::upper($symbol) . ' ' . $priceText;
        if ($changeText !== null && $changeText !== '') {
            $label .= ' (' . $changeText . ')';
        }

        return $label;
    }

    private function formatNumber(mixed $value): string
    {
        if (!is_numeric($value)) {
            return trim((string) $value);
        }

        $formatted = number_format((float) $value, 2, '.', '');
        return rtrim(rtrim($formatted, '0'), '.');
    }
}
