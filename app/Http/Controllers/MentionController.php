<?php

namespace App\Http\Controllers;

use App\Services\MentionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentionController extends Controller
{
    public function __construct(
        private readonly MentionService $mentionService,
    ) {
    }

    public function users(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if ($query === '') {
            return response()->json([
                'data' => [],
            ]);
        }

        $users = $this->mentionService->searchUsers($query, 8)
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar' => $user->profile_photo_url,
                'url' => route('users.show', $user),
            ])
            ->values();

        return response()->json([
            'data' => $users,
        ]);
    }
}
