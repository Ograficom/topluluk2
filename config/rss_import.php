<?php

return [
    'sources' => array_values(array_filter(array_map('trim', explode(',', env('RSS_IMPORT_SOURCES', ''))))),
    'user_email' => env('RSS_IMPORT_USER_EMAIL'),
    'community_slug' => env('RSS_IMPORT_COMMUNITY_SLUG'),
    'publish' => env('RSS_IMPORT_PUBLISH', false),
    'limit' => (int) env('RSS_IMPORT_LIMIT', 10),
    'ollama_url' => rtrim(env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/'),
    'ollama_model' => env('OLLAMA_MODEL', 'qwen2.5:0.5b'),
    'ollama_timeout' => (int) env('OLLAMA_TIMEOUT', 120),
];
