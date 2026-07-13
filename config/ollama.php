<?php

return [
    'driver' => env('OLLAMA_DRIVER', 'cloud'),
    'url' => env('OLLAMA_URL', env('OLLAMA_BASE_URL', 'https://ollama.com')),
    'api_key' => env('OLLAMA_API_KEY'),
    'model' => env('OLLAMA_CLOUD_MODEL', env('OLLAMA_MODEL', 'gpt-oss:20b')),
    'timeout' => env('OLLAMA_TIMEOUT', 120),
];
