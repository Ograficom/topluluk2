<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    public function index(Request $request): View
    {
        $ads = $request->user()->advertisements()->latest()->paginate(15);
        return view('advertisements.index', compact('ads'));
    }

    public function create(): View
    {
        $dailyPrice = (float) (SiteSetting::current()?->advertisement_daily_price ?? 200);

        return view('advertisements.create', compact('dailyPrice'));
    }

    public function edit(Request $request, Advertisement $advertisement): View
    {
        abort_unless($request->user()->is($advertisement->user), 403);

        return view('advertisements.create', [
            'dailyPrice' => (float) $advertisement->daily_budget,
            'advertisement' => $advertisement,
        ]);
    }

    public function update(Request $request, Advertisement $advertisement): RedirectResponse
    {
        abort_unless($request->user()->is($advertisement->user), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'remove_image' => ['sometimes', 'boolean'],
            'video_url' => ['nullable', 'url', 'max:2048', 'starts_with:https://'],
            'target_url' => ['nullable', 'url', 'max:2048', 'starts_with:https://'],
            'button_text' => ['nullable', 'string', 'max:40'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $startsAt = Carbon::parse($data['starts_at'], 'Europe/Istanbul')->utc();
        $endsAt = Carbon::parse($data['ends_at'], 'Europe/Istanbul')->utc();
        $image = $advertisement->image_path;

        if ($request->boolean('remove_image') && $image) {
            Storage::disk('public')->delete($image);
            $image = null;
        }

        if ($request->hasFile('image')) {
            $newImage = $request->file('image')->store('advertisements', 'public');
            if (! $newImage) throw new \RuntimeException('Reklam görseli kaydedilemedi.');
            if ($image) Storage::disk('public')->delete($image);
            $image = $newImage;
        }

        $advertisement->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'image_path' => $image,
            'video_url' => $data['video_url'] ?? null,
            'target_url' => $data['target_url'] ?? '',
            'button_text' => $data['button_text'] ?: 'Daha fazla bilgi al',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'total_budget' => round(max(1, (int) ceil($startsAt->diffInMinutes($endsAt) / 1440)) * (float) $advertisement->daily_budget, 2),
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->route('advertisements.index')->with('status', 'Reklamın güncellendi.');
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

    public function show(Request $request, Advertisement $advertisement): View
    {
        $isOwner = $request->user()?->is($advertisement->user);
        abort_unless($isOwner || Advertisement::query()->whereKey($advertisement->id)->deliverable()->exists(), 404);

        $advertisement->load(['user', 'post']);
        $advertisement->increment('impressions');

        return view('advertisements.show', compact('advertisement'));
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

        $advertisement->increment('clicks');
        return redirect()->away($advertisement->target_url);
    }
}
