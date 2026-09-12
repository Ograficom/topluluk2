<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class AiController extends Controller
{
    public function ask(): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'message' => 'Yapay zeka özelliği devre dışı.',
        ], 410);
    }
}
