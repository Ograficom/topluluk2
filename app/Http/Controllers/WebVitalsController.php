<?php

namespace App\Http\Controllers;

use App\Models\WebVitalMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WebVitalsController extends Controller
{
    private const METRICS = ['LCP', 'INP', 'CLS', 'FCP', 'TTFB'];

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metrics' => ['required', 'array', 'min:1', 'max:10'],
            'metrics.*.name' => ['required', 'string', 'in:LCP,INP,CLS,FCP,TTFB'],
            'metrics.*.value' => ['required', 'numeric', 'min:0', 'max:600000'],
            'metrics.*.page' => ['nullable', 'string', 'max:4096'],
            'metrics.*.route' => ['nullable', 'string', 'max:160'],
            'metrics.*.device' => ['nullable', 'string', 'in:mobile,desktop'],
            'metrics.*.connection' => ['nullable', 'string', 'max:32'],
            'metrics.*.navigation' => ['nullable', 'string', 'max:32'],
        ]);

        $requestHost = strtolower($request->getHost());
        $rows = [];

        foreach ($validated['metrics'] as $metric) {
            $pagePath = $this->safePagePath($metric['page'] ?? '/');

            if ($pagePath === null) {
                continue;
            }

            $rows[] = [
                'metric_name' => $metric['name'],
                'metric_value' => (float) $metric['value'],
                'rating' => $this->rating($metric['name'], (float) $metric['value']),
                'device_type' => $metric['device'] ?? 'desktop',
                'page_path' => $pagePath,
                'route_name' => isset($metric['route']) ? substr(trim($metric['route']), 0, 160) : null,
                'connection_type' => isset($metric['connection']) ? substr(trim($metric['connection']), 0, 32) : null,
                'navigation_type' => isset($metric['navigation']) ? substr(trim($metric['navigation']), 0, 32) : null,
                'is_secure' => $request->isSecure(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows !== []) {
            WebVitalMetric::insert($rows);
        }

        return response()->json([
            'ok' => true,
            'stored' => count($rows),
            'secure' => $request->isSecure(),
            'host' => $requestHost,
        ]);
    }

    public function dashboard(Request $request)
    {
        abort_unless(
            $request->user() && $request->user()->normalizedRole() === \App\Models\User::ROLE_ADMIN,
            403
        );

        $since = now()->subDays(28);
        $devices = ['mobile', 'desktop'];
        $metrics = ['LCP', 'INP', 'CLS'];

        $summary = [];
        foreach ($devices as $device) {
            foreach ($metrics as $metric) {
                $values = WebVitalMetric::query()
                    ->where('metric_name', $metric)
                    ->where('device_type', $device)
                    ->where('created_at', '>=', $since)
                    ->orderBy('metric_value')
                    ->limit(5000)
                    ->pluck('metric_value');

                $summary[$device][$metric] = $this->summarize($metric, $values);
            }
        }

        $allSamples = WebVitalMetric::query()
            ->where('created_at', '>=', $since)
            ->count();

        $secureSamples = WebVitalMetric::query()
            ->where('created_at', '>=', $since)
            ->where('is_secure', true)
            ->count();

        $insecureSamples = max(0, $allSamples - $secureSamples);

        $pageStats = WebVitalMetric::query()
            ->selectRaw('page_path, device_type, count(*) as samples')
            ->where('created_at', '>=', $since)
            ->groupBy('page_path', 'device_type')
            ->orderByDesc('samples')
            ->limit(20)
            ->get();

        $lastSampleAt = WebVitalMetric::query()
            ->max('created_at');

        return view('dashboard.site-health', [
            'summary' => $summary,
            'allSamples' => $allSamples,
            'secureSamples' => $secureSamples,
            'insecureSamples' => $insecureSamples,
            'pageStats' => $pageStats,
            'lastSampleAt' => $lastSampleAt,
            'since' => $since,
        ]);
    }

    private function safePagePath(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '/';
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }

        if (isset($parts['host']) && !in_array(strtolower($parts['host']), [
            'ografi.com',
            'www.ografi.com',
        ], true)) {
            return null;
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return substr($path . $query, 0, 2048);
    }

    private function rating(string $metric, float $value): string
    {
        return match ($metric) {
            'LCP' => $value <= 2500 ? 'good' : ($value <= 4000 ? 'needs-improvement' : 'poor'),
            'INP' => $value <= 200 ? 'good' : ($value <= 500 ? 'needs-improvement' : 'poor'),
            'CLS' => $value <= 0.1 ? 'good' : ($value <= 0.25 ? 'needs-improvement' : 'poor'),
            'FCP' => $value <= 1800 ? 'good' : ($value <= 3000 ? 'needs-improvement' : 'poor'),
            'TTFB' => $value <= 800 ? 'good' : ($value <= 1800 ? 'needs-improvement' : 'poor'),
            default => 'unknown',
        };
    }

    private function summarize(string $metric, Collection $values): array
    {
        $values = $values->map(fn ($value) => (float) $value)->sort()->values();
        $count = $values->count();

        if ($count === 0) {
            return [
                'count' => 0,
                'p75' => null,
                'good' => 0,
                'needs' => 0,
                'poor' => 0,
                'status' => 'no-data',
            ];
        }

        $good = 0;
        $needs = 0;
        $poor = 0;

        foreach ($values as $value) {
            match ($this->rating($metric, $value)) {
                'good' => $good++,
                'needs-improvement' => $needs++,
                'poor' => $poor++,
                default => null,
            };
        }

        $index = min($count - 1, (int) ceil($count * 0.75) - 1);
        $p75 = $values->get($index);

        return [
            'count' => $count,
            'p75' => $p75,
            'good' => $good,
            'needs' => $needs,
            'poor' => $poor,
            'good_percent' => round(($good / $count) * 100, 1),
            'status' => $this->rating($metric, $p75),
        ];
    }
}
