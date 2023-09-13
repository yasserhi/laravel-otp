<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Mobile Column
    |--------------------------------------------------------------------------
    |
    | Here you should specify name of your column (in users table) which user
    | mobile number reside in.
    |
     */
    'mobile_column'    => 'mobile',

    /*
    |--------------------------------------------------------------------------
    | Default OTP Tokens Table Name
    |--------------------------------------------------------------------------
    |
    | Here you should specify name of your OTP tokens table in database.
    | This table will held all information about created OTP tokens for users.
    |
     */
    'token_table'      => 'otp_tokens',

    /*
    |--------------------------------------------------------------------------
    | Verification Token Length
    |--------------------------------------------------------------------------
    |
    | Here you can specify length of OTP tokens which will send to users.
    |
     */
    'token_length'     => env('OTP_TOKEN_LENGTH', 5),

    /*
    |--------------------------------------------------------------------------
    | Verification Token Lifetime
    |--------------------------------------------------------------------------
    |
    | Here you can specify lifetime of OTP tokens (in minutes) which will send to users.
    |
    */
    'token_lifetime'   => env('OTP_TOKEN_LIFE_TIME', 5),

    /*
    |--------------------------------------------------------------------------
    | Throttle
    |--------------------------------------------------------------------------
    |
    | Here you can specify the number of seconds a user must wait before
    | generating more OTPs. This prevents the user from
    | quickly generating a very large amount of OTPs.
    |
    */
    'throttle'   => 60*3,

    /*
    |--------------------------------------------------------------------------
    | OTP Prefix
    |--------------------------------------------------------------------------
    |
    | Here you can specify prefix of OTP tokens for adding to cache.
    |
    */
    'prefix'           => 'otp_',

    /*
    |--------------------------------------------------------------------------
    | SMS Client (REQUIRED)
    |--------------------------------------------------------------------------
    |
    | Here you should specify your implemented "SMS Client" class. This class is responsible
    | for sending SMS to users. You may use your own sms channel, so this is not a required option anymore.
    |
    */
    'sms_client'       => '',

    /*
    |--------------------------------------------------------------------------
    |  Default SMS Notification Channel
    |--------------------------------------------------------------------------
    |
    | This is an otp default sms channel. But you may specify your own sms channel.
    | If you use default channel you must set "sms_client". Otherwise you don't need that.
    |
    */
    'channel'          => \Fouladgar\OTP\Notifications\Channels\OTPSMSChannel::class,
];
