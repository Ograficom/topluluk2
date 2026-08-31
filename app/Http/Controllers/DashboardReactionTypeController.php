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

        $reactionTypes = ReactionType::query()
            ->where('is_active', true)
            ->orderBy('label')
            ->get([
                'id',
                'submitted_by_user_id',
                'label',
                'short_code',
                'emoji',
                'gif_url',
                'is_active',
            ]);

        $myReactionTypes = ReactionType::query()
            ->where('submitted_by_user_id', $user->id)
            ->latest()
            ->get([
                'id',
                'label',
                'short_code',
                'emoji',
                'gif_url',
                'is_active',
                'created_at',
            ]);

        return view('dashboard.reactions', [
            'reactionTypes' => $reactionTypes,
            'myReactionTypes' => $myReactionTypes,
            'reactionUploadBlocked' => $user->isBlockedFrom('reaction_uploads'),
        ]);
    }

    public function all(Request $request): View
    {
        $reactionTypes = ReactionType::query()
            ->where('is_active', true)
            ->orderBy('label')
            ->get([
                'id',
                'submitted_by_user_id',
                'label',
                'short_code',
                'emoji',
                'gif_url',
                'is_active',
            ]);

        return view('dashboard.reactions-all', [
            'reactionTypes' => $reactionTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->isBlockedFrom('reaction_uploads')) {
            abort(403, 'Yeni tepki ekleme yetkiniz kisitlandi.');
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
            'reaction_image' => [
                'nullable',
                'file',
                'mimes:gif,png,jpg,jpeg,webp',
                'max:10240',
            ],
            'policy_ack' => ['accepted'],
        ], [
            'policy_ack.accepted' => 'Yeni tepki ekleme kurallarini onaylamalisiniz.',
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
            'submitted_by_user_id' => $user->id,
            'label' => trim((string) $validated['label']),
            'short_code' => $shortCode,
            'emoji' => $emoji !== '' ? $emoji : null,
            'gif_url' => $imagePath,
            'is_active' => true,
        ]);

        return redirect()
            ->route('dashboard.reactions', ['tab' => 'mine'])
            ->with('status', 'reaction-created');
    }
}
