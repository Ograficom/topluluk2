<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    public function index(Request $request): View
    {
        $ads = $request->user()->advertisements()->with(['user', 'post'])->latest()->paginate(15);
        $events = AdvertisementEvent::query()
            ->whereIn('advertisement_id', $ads->getCollection()->pluck('id'))
            ->get()
            ->groupBy('advertisement_id');

        foreach ($ads as $ad) {
            $adEvents = $events->get($ad->id, collect());
            $impressions = $adEvents->where('event_type', 'impression');
            $clicks = $adEvents->where('event_type', 'click');
            $ad->analytics = [
                'impressions' => $impressions->count(),
                'detail_views' => $adEvents->where('event_type', 'detail')->count(),
                'clicks' => $clicks->count(),
                'reads' => $adEvents->where('event_type', 'read')->count(),
                'read_seconds' => (int) $adEvents->where('event_type', 'read')->sum('seconds'),
                'returns' => $adEvents->where('event_type', 'return')->count(),
                'members' => $impressions->whereNotNull('user_id')->unique('user_id')->count(),
                'visitors' => $impressions->whereNull('user_id')->unique('visitor_token')->count(),
                'member_clicks' => $clicks->whereNotNull('user_id')->count(),
                'visitor_clicks' => $clicks->whereNull('user_id')->count(),
            ];
            $ad->analytics['ctr'] = $ad->analytics['impressions'] > 0
                ? round(($ad->analytics['clicks'] / $ad->analytics['impressions']) * 100, 2)
                : 0;
        }

        return view('advertisements.index', compact('ads'));
    }

    public function create(): View
    {
        $dailyPrice = (float) (SiteSetting::current()?->advertisement_daily_price ?? 200);

        return view('advertisements.create', compact('dailyPrice'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'video_url' => ['nullable', 'url', 'max:2048', 'starts_with:https://'],
            'target_url' => ['nullable', 'url', 'max:2048', 'starts_with:https://'],
            'button_text' => ['nullable', 'string', 'max:40'],
            'starts_at' => ['required', 'date', 'after_or_equal:today'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ], [
            'starts_at.after_or_equal' => 'Yayın başlangıcı bugünden önce olamaz.',
            'ends_at.after' => 'Yayın bitişi, yayın başlangıcından sonra olmalıdır.',
        ]);

        $startsAt = Carbon::parse($data['starts_at'], 'Europe/Istanbul')->utc();
        $endsAt = Carbon::parse($data['ends_at'], 'Europe/Istanbul')->utc();
        $durationDays = max(1, (int) ceil($startsAt->diffInMinutes($endsAt) / 1440));
        $dailyPrice = (float) (SiteSetting::current()?->advertisement_daily_price ?? 200);
        $totalPrice = round($durationDays * $dailyPrice, 2);

        $image = $request->file('image')?->store('advertisements', 'public');

        $request->user()->advertisements()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'image_path' => $image,
            'video_url' => $data['video_url'] ?? null,
            'target_url' => $data['target_url'] ?? '',
            'button_text' => $data['button_text'] ?: 'Daha fazla bilgi al',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'total_budget' => $totalPrice,
            'daily_budget' => $dailyPrice,
            'billing_type' => 'paid',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        return redirect()->route('advertisements.index')
            ->with('status', 'Reklam başvurun alındı. Yönetici onayı ve ödeme kontrolünden sonra yayınlanacak.');
    }

    public function edit(Request $request, Advertisement $advertisement): View
    {
        abort_unless($request->user()->is($advertisement->user), 403);
        $dailyPrice = (float) (SiteSetting::current()?->advertisement_daily_price ?? 200);

        return view('advertisements.edit', compact('advertisement', 'dailyPrice'));
    }

    public function update(Request $request, Advertisement $advertisement): RedirectResponse
    {
        abort_unless($request->user()->is($advertisement->user), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'video_url' => ['nullable', 'url', 'max:2048', 'starts_with:https://'],
            'target_url' => ['nullable', 'url', 'max:2048', 'starts_with:https://'],
            'button_text' => ['nullable', 'string', 'max:40'],
            'starts_at' => ['required', 'date', 'after_or_equal:today'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $image = $advertisement->image_path;
        if ($request->boolean('remove_image') && $image) {
            Storage::disk('public')->delete($image);
            $image = null;
        }
        if ($request->hasFile('image')) {
            if ($image) Storage::disk('public')->delete($image);
            $image = $request->file('image')->store('advertisements', 'public');
        }

        $startsAt = Carbon::parse($data['starts_at'], 'Europe/Istanbul')->utc();
        $endsAt = Carbon::parse($data['ends_at'], 'Europe/Istanbul')->utc();
        $days = max(1, (int) ceil($startsAt->diffInMinutes($endsAt) / 1440));
        $dailyPrice = (float) (SiteSetting::current()?->advertisement_daily_price ?? 200);

        $advertisement->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'image_path' => $image,
            'video_url' => $data['video_url'] ?? null,
            'target_url' => $data['target_url'] ?? '',
            'button_text' => $data['button_text'] ?: 'Daha fazla bilgi al',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'daily_budget' => $dailyPrice,
            'total_budget' => round($days * $dailyPrice, 2),
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ]);

        return redirect()->route('advertisements.index')
            ->with('status', 'Reklamın güncellendi ve yeniden yönetici onayına gönderildi.');
    }

    public function show(Request $request, Advertisement $advertisement): View
    {
        $isOwner = $request->user()?->is($advertisement->user);
        abort_unless($isOwner || Advertisement::query()->whereKey($advertisement->id)->deliverable()->exists(), 404);

        $advertisement->load(['user', 'post']);
        AdvertisementEvent::record($advertisement, $request, 'detail');

        $visitorToken = hash('sha256', $request->session()->getId());
        $lastClick = AdvertisementEvent::query()->where('advertisement_id', $advertisement->id)
            ->where('visitor_token', $visitorToken)->where('event_type', 'click')->latest()->first();
        $lastReturn = AdvertisementEvent::query()->where('advertisement_id', $advertisement->id)
            ->where('visitor_token', $visitorToken)->where('event_type', 'return')->latest()->first();

        if ($lastClick && (! $lastReturn || $lastClick->created_at->gt($lastReturn->created_at))) {
            AdvertisementEvent::record($advertisement, $request, 'return');
        }

        return view('advertisements.show', compact('advertisement'));
    }

    public function trackRead(Request $request, Advertisement $advertisement): \\Illuminate\\Http\\Response
    {
        abort_unless(Advertisement::query()->whereKey($advertisement->id)->deliverable()->exists(), 404);

        $sessionKey = 'advertisement_read_'.$advertisement->id;
        if (! $request->session()->has($sessionKey)) {
            $seconds = (int) $request->validate(['seconds' => ['required', 'integer', 'min:15', 'max:3600']])['seconds'];
            AdvertisementEvent::record($advertisement, $request, 'read', $seconds);
            $request->session()->put($sessionKey, true);
        }

        return response()->noContent();
    }

    public function click(Advertisement $advertisement): RedirectResponse
    {
        abort_unless($advertisement->status === 'approved'
            && $advertisement->starts_at->isPast()
            && $advertisement->ends_at->isFuture()
            && ($advertisement->billing_type === 'free'
                || ($advertisement->payment_status === 'paid' && $advertisement->spent_amount < $advertisement->total_budget)), 404);

        if (! filled($advertisement->target_url)) {
            return redirect()->route('advertisements.show', $advertisement);
        }

        AdvertisementEvent::record($advertisement, request(), 'click');
        return redirect()->away($advertisement->target_url);
    }
}
