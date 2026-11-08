<?php

namespace App\\Http\\Controllers;

use App\\Models\\Advertisement;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
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
        return view('advertisements.create');
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
            'total_budget' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'daily_budget' => ['required', 'numeric', 'min:1', 'lte:total_budget'],
        ]);

        $image = $request->file('image')?->store('advertisements', 'public');
        $request->user()->advertisements()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'image_path' => $image,
            'video_url' => $data['video_url'] ?? null,
            'target_url' => $data['target_url'],
            'button_text' => $data['button_text'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'total_budget' => $data['total_budget'],
            'daily_budget' => $data['daily_budget'],
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
