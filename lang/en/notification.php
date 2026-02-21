<?php

return [
    'admin_sent' => [
        'title' => 'Admin Notification',
        'body' => 'You have received a new notification from administration',
        'greeting' => 'Hello :name',
    ],

    // Admin Panel Translations
    'admin_panel' => [
        // Navigation & Labels
        'communication' => 'Communication',
        'notifications_management' => 'Notifications Management',
        'notification' => 'Notification',
        'notifications' => 'Notifications',
        'view_all_notifications_description' => 'View all sent notifications and send new ones to your users',
        'sent_notifications' => 'Sent Notifications',
        'sent_notification' => 'Sent Notification',
        'sent_notifications_history' => 'Sent Notifications History',
        'track_admin_notifications' => 'Track all notifications sent by administrators',
        
        // Form Fields & Sections
        'send_new_notification' => 'Send New Notification',
        'send_notifications_to_users' => 'Send notifications to users',
        'notification_title' => 'Notification Title',
        'notification_message' => 'Notification Message',
        'recipients' => 'Recipients',
        'notification_types' => 'Notification Types',
        'notification_details' => 'Notification Details',
        
        // Recipient Options
        'all_users_customers_sellers' => 'All Users (Customers & Sellers)',
        'all_customers' => 'All Customers',
        'all_sellers' => 'All Sellers',
        'specific_customers' => 'Specific Customers',
        'specific_sellers' => 'Specific Sellers',
        'select_customers' => 'Select Customers',
        'select_sellers' => 'Select Sellers',
        
        // Notification Types
        'database_notification' => 'Database Notification',
        'push_notification' => 'Push Notification',
        'database_notification_help' => 'Store notification in database (appears in user\'s notification list)',
        'push_notification_help' => 'Send as push notification to mobile devices',
        
        // Table Columns
        'title' => 'Title',
        'message' => 'Message',
        'recipient_type' => 'Recipient Type',
        'recipient' => 'Recipient',
        'read' => 'Read',
        'notification_type' => 'Notification Type',
        'sent_at' => 'Sent At',
        'read_at' => 'Read At',
        'sent_by' => 'Sent By',
        'status' => 'Status',
        'recipients_count' => 'Recipients',
        'db' => 'DB',
        'push' => 'Push',
        
        // Recipient Types
        'customer' => 'Customer',
        'seller' => 'Seller',
        'admin_user' => 'Admin User',
        'user_deleted' => 'User Deleted',
        'unknown' => 'Unknown',
        
        // Status Values
        'all_users' => 'All Users',
        'customers' => 'Customers',
        'sellers' => 'Sellers',
        'processed' => 'Processed',
        'queued' => 'Queued',
        'draft' => 'Draft',
        
        // Filters
        'read_status' => 'Read Status',
        'unread' => 'Unread',
        'sent_from' => 'Sent From',
        'sent_until' => 'Sent Until',
        'created_from' => 'Created From',
        'created_until' => 'Created Until',
        
        // Actions
        'delete' => 'Delete',
        'delete_selected' => 'Delete Selected',
        'mark_as_read' => 'Mark as Read',
        'view' => 'View',
        'notification_details_modal' => 'Notification Details',
        
        // Messages
        'no_title' => 'No Title',
        'no_message' => 'No Message',
        'additional_data' => 'Additional Data',
        
        // Widget Stats
        'total_sent' => 'Total Sent',
        'sent_today' => 'Sent Today',
        'total_recipients' => 'Total Recipients',
        'read_rate' => 'Read Rate',
        'database_notifications' => 'Database Notifications',
        'push_notifications' => 'Push Notifications',
        'admin_notifications_sent' => 'Admin notifications sent',
        'customers_and_sellers' => 'Customers & Sellers',
        'all_notifications' => 'All Notifications',
        'system_wide_notifications' => 'System-wide notifications',
        'unread_notifications' => 'Unread Notifications',
        'pending_notifications' => 'Pending notifications',
        
        // Success Messages
        'notification_sent_successfully' => 'Notification sent successfully!',
        'notifications_marked_as_read' => 'Selected notifications marked as read',
        'notifications_deleted' => 'Selected notifications deleted',
        'notification_queued_successfully' => 'Notification Queued Successfully',
        'notification_queued_to_recipients' => 'Notification queued to be sent to :count recipients',
    ],

    'company' => [
        'new_applicant' => [
            'title' => 'New Application Request',
            'body' => 'There is a new application for the job :job from the user :applicant'
        ]
    ],
    'seeker' => [
        'new_applicant' => [
            'success' => 'Application Submitted Successfully',
            'not_valid' => 'After reviewing your CV, you are not eligible to apply for this job',
            'must_complete_cv' => 'You must complete your CV! And come back to apply again'
        ],
        'short_list' => [
            'title' => 'Congratulations, you have been added to the shortlist',
            'body' => 'Congratulations, you have been added to the shortlist for the job application :job',
            'removed' => 'The application has been removed from the shortlist for the job :job'
        ] ,
        'interview_list' => [
            'title' => 'You have been nominated for an interview',
            'body' => 'Congratulations, you have been nominated for an interview for the job :job',
            'removed' => 'Your nomination for an interview has been cancelled for the job application :job'

        ]
    ],
    'admin' => [
        'cv_actions'=>
            [
                'add new skill'=>'Add New Skill',
                'add new language'=>'Add New Language',
                'add new field'=>'Add New Field',
                'add new reference'=>'Add New Reference',
                'add new'=>'Add New',
                'add new experience'=>'add new experience',
                ]

    ],
    'main'=>[
        'newsletter'=>[
            'success'=>'You have successfully subscribed to the newsletter'
        ],
        'contact'=>[
            'send_success'=>'Sent Successfully',
            'send'=>'Send',
            'message'=>'Message Text',
            'nick_name'=>'Nickname',
            'consulting_text'=>'Consulting Text',
            'consulting_header_text'=>'Tell us about the consultation you would like information about',
        ],
        'accounts'=>[
            'created_successfully'=>'Your account has been created successfully, wait for the activation message via email',
            'company_created_successfully'=>'Your company account has been created successfully, please log in and add jobs',

        ],
        'empty_label'=> "No Data Available!",


    ],
    'new_message' => [
        'title' => 'New Message',
        'body' => 'You have :count new messages from :user to request :service',
        'body_single' => 'You have a new message from :user to request :service',
        'action' => 'View',
        'greeting' => 'Hello :name',
    ],

    'new_estimate' => [
        'title' => 'New Estimate',
        'body' => ':seller has sent you a new estimate for the request :service',
        'action' => 'View',
        'greeting' => 'Hello :name',
    ],

    'new_response' => [
        'title' => 'New Response',
        'body' => ':seller has sent you a new response for the request :service',
        'action' => 'View',
        'greeting' => 'Hello :name',
    ],

    'new_invitation' => [
        'title' => 'New Invitation',
        'body' => ':customer has sent you a new invitation for the request :service',
        'action' => 'View',
        'greeting' => 'Hello :name',
    ],

    'request_changed' => [
        'title' => 'Request Status Changed',
        'body' => 'The status of the request :service has been changed to :status',
        'action' => 'View',
        'greeting' => 'Hello :name',
    ],

    'response_changed' => [
        'title' => 'Response Status Changed',
        'body' => 'The status of the response for the request :service has been changed to :status',
        'action' => 'View',
        'greeting' => 'Hello :name',
    ],

    'new_request' => [
        'title' => 'New Request',
        'body' => 'There is a new request that matches your preferences.',
        'action' => 'View',
        'greeting' => 'Hello :name',
    ],

    'created_seller_rate' => [
        'title' => 'Rating Created',
        'body' => 'You have received a new rating for the service :service',
        'action' => 'View',
        'greeting' => 'Hello :name',
    ],

    'updated_seller_rate' => [
        'title' => 'Rating Updated',
        'body' => 'You have received an updated rating for the service :service',
        'action' => 'View',
        'greeting' => 'Hello :name',
    ],


];
