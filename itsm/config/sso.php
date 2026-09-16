<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SSO Secret Key
    |--------------------------------------------------------------------------
    |
    | Shared secret key between portal and this system.
    | MUST be the same value as configured in the portal.
    |
    */

    'secret' => env('SSO_SECRET', 'amarin-sso-shared-secret-key-2024'),

    /*
    |--------------------------------------------------------------------------
    | Token Expiry (seconds)
    |--------------------------------------------------------------------------
    |
    | How long the SSO token is valid. Default: 60 seconds.
    | Must match the portal's configuration.
    |
    */

    'token_expiry' => env('SSO_TOKEN_EXPIRY', 60),

    /*
    |--------------------------------------------------------------------------
    | Portal URL
    |--------------------------------------------------------------------------
    |
    | The URL of the Amarin Portal for redirects.
    |
    */

    'portal_url' => env('SSO_PORTAL_URL', 'https://portal.amarin.biz.id'),

    /*
    |--------------------------------------------------------------------------
    | After Login Redirect
    |--------------------------------------------------------------------------
    |
    | Where to redirect after successful SSO login.
    |
    */

    'redirect_after_login' => env('SSO_REDIRECT_AFTER_LOGIN', '/dashboard'),

    /*
    |--------------------------------------------------------------------------
    | Employee Email Column
    |--------------------------------------------------------------------------
    |
    | The column name in your user/employee table that stores the work email.
    |
    */

    'email_column' => env('SSO_EMAIL_COLUMN', 'email'),

    /*
    |--------------------------------------------------------------------------
    | Auth Guard
    |--------------------------------------------------------------------------
    |
    | The authentication guard to use for SSO login.
    |
    */

    'guard' => env('SSO_AUTH_GUARD', 'web'),

    /*
    |--------------------------------------------------------------------------
    | Access Column
    |--------------------------------------------------------------------------
    |
    | Column in tbl_employee that controls access to this application.
    | SSO login will be denied if the column value is 0/null.
    |
    */

    'access_column' => env('SSO_ACCESS_COLUMN', 'access_itsm'),


    /*
    |--------------------------------------------------------------------------
    | Master Employee Database
    |--------------------------------------------------------------------------
    |
    | The master employee database (db_master_amarin_original) is registered
    | under different connection names across internal systems. The gate walks
    | this list and uses the first connection that exists and can be queried.
    |
    | Set SSO_MASTER_CONNECTION in .env to pin one specific connection.
    |
    */

    'master_connections' => array_values(array_filter([
        env('SSO_MASTER_CONNECTION'),
        'master',
        'amarin',
        'mysql_master',
        'master_employee',
        'master_amarin',
        'mysql_amarin',
        'mysql_master_amarin',
        'mysql_second',
        'db_master_amarin_original',
    ])),

    /*
    |--------------------------------------------------------------------------
    | Employee Table & Email Column (master DB)
    |--------------------------------------------------------------------------
    */

    'employee_table' => env('SSO_EMPLOYEE_TABLE', 'tbl_employee'),

    'master_email_column' => env('SSO_MASTER_EMAIL_COLUMN', 'email_work'),

];
