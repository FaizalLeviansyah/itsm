<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'whatsapp' => [
        'api_url' => env('WA_API_URL', 'https://wa.amarin.biz.id/message/send-text'),
        'session' => env('WA_SESSION', 'notif'),
        'default_to' => env('WA_DEFAULT_TO', '628563339320'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SOC Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Security Operations Center (SOC) API integration
    | that syncs endpoint data with ITSM assets.
    |
    */

    'soc' => [
        'api_url' => env('SOC_API_URL', 'https://soc.amarin.biz.id/api/integration/endpoints'),
        'api_key' => env('SOC_API_KEY', 'FarhanGantengj@y@'),
    ],

];
