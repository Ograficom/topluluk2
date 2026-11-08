<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Http\\Request;

class AdvertisementEvent extends Model
{
    protected $fillable = [
        'advertisement_id',
        'user_id',
        'visitor_token',
        'event_type',
        'seconds',
        'referrer_host',
    ];

    public static function record(Advertisement $advertisement, Request $request, string $type, int $seconds = 0): ?self
    {
        $visitorToken = hash('sha256', $request->session()->getId());
        $userId = $request->user()?->id;

        if ($type === 'impression') {
            $alreadyCounted = self::query()
                ->where('advertisement_id', $advertisement->id)
                ->where('visitor_token', $visitorToken)
                ->where('event_type', 'impression')
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();

            if ($alreadyCounted) {
                return null;
            }

            $advertisement->increment('impressions');
        }

        if ($type === 'click') {
            $advertisement->increment('clicks');
        }

        return self::query()->create([
            'advertisement_id' => $advertisement->id,
            'user_id' => $userId,
            'visitor_token' => $visitorToken,
            'event_type' => $type,
            'seconds' => min(max($seconds, 0), 65535),
            'referrer_host' => $request->headers->has('referer')
                ? parse_url((string) $request->headers->get('referer'), PHP_URL_HOST)
                : null,
        ]);
    }

    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
