<?php

namespace App\Http\Controllers;

use App\Models\ReactionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DashboardReactionTypeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $activeReactions = ReactionType::query()
            ->where('is_active', true)
            ->where('moderation_status', ReactionType::STATUS_APPROVED)
            ->orderBy('label')
            ->get(['id', 'label', 'short_code', 'emoji', 'gif_url']);

        $mySubmissions = ReactionType::query()
            ->where('submitted_by_user_id', $user->id)
            ->latest()
            ->get([
                'id',
                'label',
                'short_code',
                'emoji',
                'gif_url',
                'is_active',
                'moderation_status',
                'moderation_note',
                'created_at',
            ]);

        return view('dashboard.reactions', [
            'activeReactions' => $activeReactions,
            'mySubmissions' => $mySubmissions,
            'reactionSubmissionBlocked' => $user->isBlockedFrom('reactions'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isBlockedFrom('reactions')) {
            abort(403, 'Tepki ekleme yetkiniz kisitlandi.');
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'emoji' => [
                'nullable',
                'string',
                'max:16',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && preg_match('/[<>]/u', $value)) {
                        $fail('Emoji alani HTML veya etiket iceremez.');
                    }
                },
            ],
            'reaction_image' => ['nullable', 'file', 'mimes:gif,png,jpg,jpeg,webp', 'max:10240'],
        ]);

        $emoji = trim((string) ($validated['emoji'] ?? ''));
        $image = $request->file('reaction_image');

        if ($emoji === '' && ! $image) {
            throw ValidationException::withMessages([
                'emoji' => 'Bir emoji yazin veya GIF/resim yukleyin.',
            ]);
        }

        $baseCode = Str::slug((string) $validated['label'], '_');
        if ($baseCode === '') {
            $baseCode = 'reaction';
        }

        $baseCode = Str::limit($baseCode, 20, '');
        $shortCode = strtolower($baseCode.'_u'.$user->id.'_'.Str::random(6));

        while (ReactionType::query()->where('short_code', $shortCode)->exists()) {
            $shortCode = strtolower($baseCode.'_u'.$user->id.'_'.Str::random(6));
        }

        $imagePath = null;
        if ($image) {
            $extension = strtolower((string) $image->getClientOriginalExtension());
            $filename = Str::uuid()->toString().'.'.$extension;
            $imagePath = $image->storeAs('reaction-types', $filename, 'public');
        }

        ReactionType::query()->create([
            'label' => trim((string) $validated['label']),
            'short_code' => $shortCode,
            'emoji' => $emoji !== '' ? $emoji : null,
            'gif_url' => $imagePath,
            'submitted_by_user_id' => $user->id,
            'moderation_status' => ReactionType::STATUS_PENDING,
            'moderation_note' => null,
            'is_active' => false,
        ]);

        return back()->with('status', 'reaction-submitted');
    }
}
