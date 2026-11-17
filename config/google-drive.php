<?php
return [
    'enabled' => (bool) env('GOOGLE_DRIVE_ENABLED', false),
    'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_DRIVE_REDIRECT_URI', env('APP_URL').'/auth/google-drive/callback'),
    'scopes' => ['https://www.googleapis.com/auth/drive.file'],
    'root_folder_name' => env('GOOGLE_DRIVE_ROOT_FOLDER', 'OGRAFI_STORAGE'),
    'backup_enabled' => (bool) env('GOOGLE_DRIVE_BACKUP_ENABLED', true),
    'backup_retention' => (int) env('GOOGLE_DRIVE_BACKUP_RETENTION', 30),
];