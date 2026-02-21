<?php

return [
    'otp_code' => 'OTP Code',

    'mail' => [
        'subject' => 'OTP Code',
        'greeting' => 'Hello!',
        'line1' => 'Your OTP code is: :code',
        'line2' => 'This code will be valid for :seconds seconds.',
        'line3' => 'If you did not request a code, please ignore this email.',
        'salutation' => 'Best Regards, :app_name',
        'alert_text' => ' Welcome! To enhance your experience, consider verifying your email address. Click the link',
    ],

    'view' => [
        'time_left' => 'seconds left',
        'resend_code' => 'Resend Code',
        'send_code' => 'Send Code',
        'verify' => 'Verify',
        'go_back' => 'Go Back',
        'change_phone_number' => 'Change phone number',
    ],

    'notifications' => [
        'title' => 'OTP Code Sent',
        'body' => 'The verification code has been sent to your e-mail address[:to]. It will be valid in :seconds seconds.',
        'sms_text' => 'Verification code has been sent via SMS to your phone number. [:to] It will be valid for :seconds seconds.',
    ],

    'validation' => [
        'invalid_code' => 'The code you entered is invalid.',
        'expired_code' => 'The code you entered has expired.',
        'temporarily_disabled' => 'Please be informed that OTP requests have been temporarily suspended to protect your data. Please try again :time . Thank you for your cooperation.',

    ],
];
