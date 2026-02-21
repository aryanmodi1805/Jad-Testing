<?php

return [
    'admin_sent' => [
        'title' => 'إشعار من الإدارة',
        'body' => 'لقد تلقيت إشعار جديد من الإدارة',
        'greeting' => 'مرحباً :name',
    ],

    // تراجم لوحة الإدارة
    'admin_panel' => [
        // التنقل والتسميات
        'communication' => 'التواصل',
        'notifications_management' => 'إدارة الإشعارات',
        'notification' => 'إشعار',
        'notifications' => 'الإشعارات',
        'view_all_notifications_description' => 'عرض جميع الإشعارات المرسلة وإرسال إشعارات جديدة للمستخدمين',
        'sent_notifications' => 'الإشعارات المرسلة',
        'sent_notification' => 'إشعار مرسل',
        'sent_notifications_history' => 'تاريخ الإشعارات المرسلة',
        'track_admin_notifications' => 'تتبع جميع الإشعارات المرسلة من قبل المديرين',
        
        // حقول النموذج والأقسام
        'send_new_notification' => 'إرسال إشعار جديد',
        'send_notifications_to_users' => 'إرسال إشعارات للمستخدمين',
        'notification_title' => 'عنوان الإشعار',
        'notification_message' => 'رسالة الإشعار',
        'recipients' => 'المستلمون',
        'notification_types' => 'أنواع الإشعارات',
        'notification_details' => 'تفاصيل الإشعار',
        
        // خيارات المستلمين
        'all_users_customers_sellers' => 'جميع المستخدمين (العملاء والبائعين)',
        'all_customers' => 'جميع العملاء',
        'all_sellers' => 'جميع البائعين',
        'specific_customers' => 'عملاء محددون',
        'specific_sellers' => 'بائعون محددون',
        'select_customers' => 'اختر العملاء',
        'select_sellers' => 'اختر البائعين',
        
        // أنواع الإشعارات
        'database_notification' => 'إشعار قاعدة البيانات',
        'push_notification' => 'إشعار فوري',
        'database_notification_help' => 'حفظ الإشعار في قاعدة البيانات (يظهر في قائمة إشعارات المستخدم)',
        'push_notification_help' => 'إرسال كإشعار فوري للأجهزة المحمولة',
        
        // أعمدة الجدول
        'title' => 'العنوان',
        'message' => 'الرسالة',
        'recipient_type' => 'نوع المستلم',
        'recipient' => 'المستلم',
        'read' => 'مقروء',
        'notification_type' => 'نوع الإشعار',
        'sent_at' => 'تاريخ الإرسال',
        'read_at' => 'تاريخ القراءة',
        'sent_by' => 'أرسل بواسطة',
        'status' => 'الحالة',
        'recipients_count' => 'عدد المستلمين',
        'db' => 'قاعدة البيانات',
        'push' => 'فوري',
        
        // أنواع المستلمين
        'customer' => 'عميل',
        'seller' => 'بائع',
        'admin_user' => 'مستخدم إداري',
        'user_deleted' => 'مستخدم محذوف',
        'unknown' => 'غير معروف',
        
        // قيم الحالة
        'all_users' => 'جميع المستخدمين',
        'customers' => 'العملاء',
        'sellers' => 'البائعون',
        'processed' => 'تم المعالجة',
        'queued' => 'في الطابور',
        'draft' => 'مسودة',
        
        // المرشحات
        'read_status' => 'حالة القراءة',
        'unread' => 'غير مقروء',
        'sent_from' => 'أرسل من',
        'sent_until' => 'أرسل حتى',
        'created_from' => 'أنشئ من',
        'created_until' => 'أنشئ حتى',
        
        // الإجراءات
        'delete' => 'حذف',
        'delete_selected' => 'حذف المحدد',
        'mark_as_read' => 'تعليم كمقروء',
        'view' => 'عرض',
        'notification_details_modal' => 'تفاصيل الإشعار',
        
        // الرسائل
        'no_title' => 'لا يوجد عنوان',
        'no_message' => 'لا توجد رسالة',
        'additional_data' => 'بيانات إضافية',
        
        // إحصائيات الودجت
        'total_sent' => 'إجمالي المرسل',
        'sent_today' => 'أرسل اليوم',
        'total_recipients' => 'إجمالي المستلمين',
        'read_rate' => 'معدل القراءة',
        'database_notifications' => 'إشعارات قاعدة البيانات',
        'push_notifications' => 'الإشعارات الفورية',
        'admin_notifications_sent' => 'إشعارات الإدارة المرسلة',
        'customers_and_sellers' => 'العملاء والبائعون',
        'all_notifications' => 'جميع الإشعارات',
        'system_wide_notifications' => 'الإشعارات على مستوى النظام',
        'unread_notifications' => 'الإشعارات غير المقروءة',
        'pending_notifications' => 'الإشعارات المعلقة',
        
        // رسائل النجاح
        'notification_sent_successfully' => 'تم إرسال الإشعار بنجاح!',
        'notifications_marked_as_read' => 'تم تعليم الإشعارات المحددة كمقروءة',
        'notifications_deleted' => 'تم حذف الإشعارات المحددة',
        'notification_queued_successfully' => 'تم إدراج الإشعار في الطابور بنجاح',
        'notification_queued_to_recipients' => 'تم إدراج الإشعار في الطابور ليتم إرساله إلى :count من المستلمين',
    ],

    'company' => [
        'new_applicant' => [
            'title' => 'طلب تقديم جديد',
            'body' => 'هناك طلب تقديم جديد لوظيفة :job عبر المستخدم :applicant'
        ]
    ],
    'seeker' => [
        'new_applicant' => [
            'success' => 'تم التقديم بنجاح',
            'not_valid' => 'بعد النظر إلى سيرتك الذاتية انت لست مخول للتقديم على هذه الوظيفة',
            'must_complete_cv' => 'يجب اكمال سيرتك الذاتية ! والعودة للتقديم مرة اخرى'
        ],
        'short_list' => [
            'title' => 'تهانينا ، تم اضافتك الى القائمة المختصرة',
            'body' => ' تهانينا ، تم اضافتك الى القائمة المختصرة بطلب تقديم لوظيفة :job',
            'removed' => '  تم حذف الطلب من القائمة المختصرة  لوظيفة :job'
        ] ,
        'interview_list' => [
            'title' => 'تم ترشيحك للمقابلة',
            'body' => 'تهانينا ، تم ترشيحك للمقابلة لوظيفة :job',
            'removed' => '  تم الغاء ترشيحك للمفابلة في طلب التقديم لوظيفة :job'

        ]
    ],
    'admin' => [
        'cv_actions'=>
        [
            'add new skill'=>'اضافة مهارة جديدة',
            'add new language'=>'اضافة لغة جديدة',
            'add new field'=>'اضافة مجال جديد',
            'add new reference'=>'اضافة معرف جديد',
            'add new'=>'اضافة جديد',
            'add new experience'=>'اضافة خبرة جديدة',

        ]

    ],
    'main'=>[
        'newsletter'=>[
            'success'=>'لقد اشتركت في النشرة الإخبارية بنجاح'
        ],
        'contact'=>[
            'send_success'=>'تم الارسال بنجاح',
            'send'=>'ارســــــال',
            'message'=>'نص الرسالة',
            'nick_name'=>'اللقب',
            'consulting_text'=>'نص الاستشارة',
            'consulting_header_text'=>'اطلعنا بالاستشارة التي تحب ان نعطيك معلومات عنها',
        ],
        'accounts'=>[
            'created_successfully'=>'تم انشاء حسابك بنجاح انتظر رساله التفعيل عبر الايميل',
            'company_created_successfully'=>'تم انشاء حساب شركتكم بنجاح، الرجاء تسجيل الدخول واضافة الوظائف',

        ],
        'empty_label'=> 'لاتوجد بيانات ! ',


    ],

    'new_message' => [
        'title' => 'رسالة جديدة',
        'body' => 'لديك عدد :count رسالة/رسائل جديدة من :user لطلب :service',
        'body_single' => 'لديك رسالة جديدة من :user لطلب :service',
        'action' => 'عرض',
        'greeting' => 'مرحباً :name',

    ],
    'new_estimate' => [
        'title' => 'تقدير سعر جديد',
        'body' => ':seller قد ارسل تقدير سعر جديد لطلب :service',
        'action' => 'عرض',
        'greeting' => 'مرجباً :name',
    ],

    'new_response' => [
        'title' => 'إستجابة جديدة لطلبك',
        'body' => ':seller قد ارسل استجابة جديدة لطلب :service',
        'action' => 'عرض',
        'greeting' => 'مرجباً :name',
    ],

    'new_invitation' => [
        'title' => 'دعوة جديدة',
        'body' => ':customer قد ارسل دعوة جديدة لطلب :service',
        'action' => 'عرض',
        'greeting' => 'مرجباً :name',
    ],

    'request_changed' => [
        'title' => 'تغير حالة الطلب',
        'body' => 'تم تغيير حالة الطلب :service الى :status',
        'action' => 'عرض',
        'greeting' => 'مرحباً :name',
    ],

    'response_changed' => [
        'title' => 'تغير حالة الاستجابة',
        'body' => 'تم تغيير حالة الاستجابة لطلب :service الى :status',
        'action' => 'عرض',
        'greeting' => 'مرحباً :name',
    ],

    'new_request' => [
        'title' => 'طلب جديد',
        'body' => 'هناك طلب جديد يتناسب مع تفضيلاتك',
        'action' => 'عرض',
        'greeting' => 'مرحباً :name',
    ],

    'created_seller_rate' => [
        'title' => 'تم انشاء تقييم',
        'body' => 'لقد تلقيت تقييم جديد للخدمة :service',
        'action' => 'عرض',
        'greeting' => 'مرحباً :name',
    ],

    'updated_seller_rate' => [
        'title' => 'تم تحديث تقييم',
        'body' => 'تم تحديث تقييم للخدمة :service',
        'action' => 'عرض',
        'greeting' => 'مرحباً :name',
    ],


];
