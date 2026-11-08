<?php

namespace App\\Http\\Controllers;

use App\\Models\\Advertisement;
use App\\Models\\SiteSetting;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Carbon;
use Illuminate\\View\\View;

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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'video_url' => ['nullable', 'url', 'max:2048', 'starts_with:https://'],
            'target_url' => ['required', 'url', 'max:2048', 'starts_with:https://'],
            'button_text' => ['required', 'string', 'max:40'],
            'starts_at' => ['required', 'date', 'after_or_equal:today'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ], [
            'starts_at.after_or_equal' => 'Yayın başlangıcı bugünden önce olamaz.',
            'ends_at.after' => 'Yayın bitişi, yayın başlangıcından sonra olmalıdır.',
        ]);

        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);
        $durationDays = max(1, (int) ceil($startsAt->diffInMinutes($endsAt) / 1440));
        $dailyPrice = (float) (SiteSetting::current()?->advertisement_daily_price ?? 200);
        $totalPrice = round($durationDays * $dailyPrice, 2);

        $image = $request->file('image')?->store('advertisements', 'public');

        $request->user()->advertisements()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'image_path' => $image,
            'video_url' => $data['video_url'] ?? null,
            'target_url' => $data['target_url'],
            'button_text' => $data['button_text'],
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

    public function click(Advertisement $advertisement): RedirectResponse
    {
        abort_unless($advertisement->status === 'approved'
            && $advertisement->starts_at->isPast()
            && $advertisement->ends_at->isFuture()
            && ($advertisement->billing_type === 'free'
                || ($advertisement->payment_status === 'paid' && $advertisement->spent_amount < $advertisement->total_budget)), 404);

        $advertisement->increment('clicks');
        return redirect()->away($advertisement->target_url);
    }
}
