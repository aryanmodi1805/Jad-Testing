<?php

return [

    'title' => 'تسجيل الدخول',

    'heading' => 'الدخول إلى حسابك',

    'actions' => [

        'register' => [
            'before' => 'أو',
            'label' => 'إنشاء حساب',
        ],

        'request_password_reset' => [
            'label' => 'نسيت كلمة المرور؟',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'البريد الإلكتروني',
        ],

        'password' => [
            'label' => 'كلمة المرور',
        ],

        'remember' => [
            'label' => 'تذكرني',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'تسجيل الدخول',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة. يرجى المحاولة مرة أخرى.',
        'blocked' => 'هذا الحساب محظور لمخالفة سياسات الموقع .',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'لقد قمت بمحاولات تسجيل دخول كثيرة جدًا',
            'body' => 'يرجى المحاولة مرة أخرى بعد :seconds ثواني.',
        ],

    ],

];
