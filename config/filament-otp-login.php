<?php

return [
    'table_name' => 'otp_codes',
    'otp_colum_name' => 'phone',// if channel email change it  to email , phone if channel sms

    'channel' => 'sms', //   email or sms
    'attempt_limit' => [
        'maxAttempts' => 3,
        'maxAttempts_email' => 5000,
        'cooldownDuration' => 5, //   in minutes
    ],

    'otp_code' => [
        'length' => env('OTP_LOGIN_CODE_LENGTH', 6),
        'expires' => env('OTP_LOGIN_CODE_EXPIRES_SECONDS', 120),
    ],
];
