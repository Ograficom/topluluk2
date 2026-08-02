<?php

return [
    'driver' => env('OLLAMA_DRIVER', 'cloud'),
    'url' => env('OLLAMA_URL', env('OLLAMA_BASE_URL', 'https://ollama.com')),
    'api_key' => env('OLLAMA_API_KEY'),
    'model' => env('OLLAMA_CLOUD_MODEL', env('OLLAMA_MODEL', 'gpt-oss:20b')),
    'vision_model' => env('OLLAMA_VISION_MODEL', 'gemma4:31b'),
    'timeout' => env('OLLAMA_TIMEOUT', 120),

    'bot' => [
        'name' => env('OLLAMA_BOT_NAME', 'Ografi AI'),
        'username' => env('OLLAMA_BOT_USERNAME', 'ografi-ai'),
        'email' => env('OLLAMA_BOT_EMAIL', 'ai-editor@ografi.com'),
    ],
];
