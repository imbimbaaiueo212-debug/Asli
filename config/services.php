    <?php

    return [

        'postmark' => [
            'token' => env('POSTMARK_TOKEN'),
        ],

        'resend' => [
            'key' => env('RESEND_KEY'),
        ],

        'ses' => [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ],

        'slack' => [
            'notifications' => [
                'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
                'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
            ],
        ],

        // ✅ Tambahkan ini
'google' => [
    'service' => [
        'enable' => env('GOOGLE_SERVICE_ENABLED', true),
        'file' => env('GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION'),
    ],
    'sheets' => [
        'spreadsheet_id' => env('GOOGLE_SHEET_ID'),
        'sheet_name' => env('GOOGLE_SHEETS_SHEET_NAME', 'Form Responses 1'),
    ],
],
        // ✅ Selesai menambahkan

    ];
