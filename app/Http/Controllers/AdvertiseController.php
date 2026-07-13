<?php

namespace App\Http\Controllers;

use App\Models\AdOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdvertiseController extends Controller
{
    private const PLACEMENTS = [
        'sidebar_top' => [
            'label' => 'Sağ sidebar üst',
            'width' => 304,
            'height' => 380,
            'description' => 'Masaüstünde sağ kolonda yorumların üstünde görünür.',
        ],
        'sidebar_story' => [
            'label' => 'Sağ sidebar orta',
            'width' => 304,
            'height' => 540,
            'description' => 'Masaüstünde sağ kolonda yorumlar ile etiketler arasında görünür.',
        ],
        'feed_inline' => [
            'label' => 'Akış içi',
            'width' => 656,
            'height' => 369,
            'description' => 'Ana akışta 16:9 geniş reklam alanı olarak görünür.',
        ],
        'mobile_inline' => [
            'label' => 'Mobil akış',
            'width' => 360,
            'height' => 203,
            'description' => 'Mobil ekranda akış içinde 16:9 alan olarak görünür.',
        ],
    ];

    private const DURATIONS = [
        7 => 35000,
        14 => 60000,
        30 => 110000,
    ];

    public function create(): View
    {
        return view('advertise.create', [
            'placements' => self::PLACEMENTS,
            'durations' => self::DURATIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'placement' => ['required', 'string', 'in:' . implode(',', array_keys(self::PLACEMENTS))],
            'duration_days' => ['required', 'integer', 'in:' . implode(',', array_keys(self::DURATIONS))],
            'title' => ['nullable', 'string', 'max:80'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $placement = self::PLACEMENTS[$validated['placement']];
        $durationDays = (int) $validated['duration_days'];
        $disk = Storage::disk('public');

        $disk->makeDirectory('ads/orders');

        $imagePath = $request->file('image')->store('ads/orders', 'public');

        if (! $imagePath) {
            return back()
                ->withInput()
                ->withErrors(['image' => 'Reklam görseli kaydedilemedi. Lütfen tekrar deneyin.']);
        }

        $order = AdOrder::query()->create([
            'user_id' => Auth::id(),
            'placement' => $validated['placement'],
            'duration_days' => $durationDays,
            'width' => $placement['width'],
            'height' => $placement['height'],
            'price_cents' => self::DURATIONS[$durationDays],
            'currency' => 'TRY',
            'title' => $validated['title'] ?? null,
            'target_url' => $validated['target_url'] ?? null,
            'image_path' => $imagePath,
            'status' => 'pending_payment',
        ]);

        return redirect()->route('advertise.payment', $order);
    }

    public function payment(AdOrder $adOrder): View
    {
        return view('advertise.payment', [
            'adOrder' => $adOrder,
            'placement' => self::PLACEMENTS[$adOrder->placement] ?? null,
        ]);
    }
}
