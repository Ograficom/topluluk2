<?php

namespace App\Http\Controllers;

use App\Services\OllamaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function ask(Request $request, OllamaService $ollama): JsonResponse
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

            $answer = $ollama->chat($messages);

            return response()->json([
                'ok' => true,
                'answer' => $answer,
            ]);
        } catch (\Throwable $e) {
            Log::error('Ografi Ollama Cloud AI hatası', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Yapay zeka şu anda cevap veremiyor. API key, model veya limitleri kontrol et.',
            ], 500);
        }
    }
}