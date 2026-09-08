<?php

namespace App\Http\Controllers;

use App\Services\AI\OpenAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function ask(Request $request, OpenAiService $openAi): JsonResponse
    {
        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:40'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        try {
            $clientMessages = collect($validated['messages'])
                ->map(fn ($message) => [
                    'role' => $message['role'],
                    'content' => trim($message['content']),
                ])
                ->filter(fn ($message) => $message['content'] !== '')
                ->values()
                ->all();

            $clientMessages = array_slice($clientMessages, -24);

            $messages = array_merge([
                [
                    'role' => 'system',
                    'content' => 'Sen Ografi.com içinde çalışan Türkçe yapay zeka yardımcısısın. Önceki mesajları dikkate al. Kısa, net ve anlaşılır cevap ver.',
                ],
            ], $clientMessages);

            $answer = $openAi->chat($messages);

            return response()->json([
                'ok' => true,
                'answer' => $answer,
            ]);
        } catch (\Throwable $e) {
            Log::error('Ografi OpenAI hatası', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Yapay zeka şu anda cevap veremiyor. OpenAI API key, model veya limitlerini kontrol et.',
            ], 500);
        }
    }
}
