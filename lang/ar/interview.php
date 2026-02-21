<?php

return [
    'interviews_count' => 'عدد المقابلات',
    'room' => 'الجناح',
    'room_number' => 'رقم الجناح',
    'interviews' => 'المقابلات',
    'interviews_list' => 'قائمة المقابلات',
    'interview_start_time' => 'موعد بدء المقابلة',
    'current_location' => [
       'absent'=>'لم يحضر بعد',
     //  'entering_main_gate';
       'workshop'=>'ورشة العمل',
      // 'recruitment';
       'waiting'=>'في الانتظار',
       'in_interview'=>'في مقابلة',
    ],
    'meeting_day_types' => [
        '7'=>'يوم كامل',
        'half'=>'نصف يوم من :from إلى :to',
        '4'=>'نصف يوم من ٨ إلى ١٢',
       // 'recruitment';
       '3'=>'نصف يوم من ١٢ إلى ٣',
     ],

     'reject_reason' => [
        'r1'=>'ضعف التحضير.',
        'r2'=>'ضعف التواصل.',
        'r3'=>'مستوى اللغة الإنكليزية ضعيف.',
        'r4'=>'قلة الخبرة أو المهارات.',
        'r5'=>'سوء التصرف أو عدم الاحترافية.',
        'custom'=>'اخرى',
     ],


    'sign-up' => 'إنشاء حساب',
    'company_account' => 'إنشاء حساب شركة',
    'seeker_account' => 'إنشاء حساب طالب وظيفة',
    'reject' => 'حالة الرفض',
    'reject_lable' => 'رفض',
    'reject_status' => [0 => 'نعم', 1 => 'لا'],
    'rejected_comments' => 'رسالة الرفض',
    'rejected_reason' => 'سبب الرفض',
    'end_interview' => '  نتيجة المقابلة',
    'call' => '  استدعاء  ',
    'Time Remaining' => 'الوقت المتبقي',
    'The current interview must be completed first for another advanced call' => 'يجب انهاء المقابله الحاليه اولاً للاتصال المتقدم اخر',
    'errors'=>[
        'inInterview'=>"هذا المستخدم في مقابلة حالياً يرجى اختيار متقدم اخر",
     ],

];
