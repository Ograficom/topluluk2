<?php

return [
    'file_key' => env('FIGMA_FILE_KEY'),
    'access_token' => env('FIGMA_ACCESS_TOKEN'),
    'webhook_passcode' => env('FIGMA_WEBHOOK_PASSCODE'),

    'github' => [
        'repository' => env('FIGMA_SYNC_GITHUB_REPOSITORY', 'Ograficom/topluluk2'),
        'token' => env('FIGMA_SYNC_GITHUB_TOKEN'),
        'event_type' => env('FIGMA_SYNC_GITHUB_EVENT', 'figma_update'),
    ],
];
