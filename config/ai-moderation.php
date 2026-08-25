<?php

return [
    'enabled' => env('AI_MODERATION_ENABLED', true),
    'scan_limit' => (int) env('AI_MODERATION_SCAN_LIMIT', 8),
    'lookback_days' => (int) env('AI_MODERATION_LOOKBACK_DAYS', 14),
    'auto_quarantine_score' => (int) env('AI_MODERATION_AUTO_QUARANTINE_SCORE', 85),
];
