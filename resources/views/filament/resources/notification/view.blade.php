@php
    $data = $record->data;
    $title = $data['title'] ?? $data['subject'] ?? __('notification.admin_panel.no_title');
    $body = $data['body'] ?? $data['message'] ?? __('notification.admin_panel.no_message');
    $notifiableType = match($record->notifiable_type) {
        'App\\Models\\Customer' => __('notification.admin_panel.customer'),
        'App\\Models\\Seller' => __('notification.admin_panel.seller'),
        'App\\Models\\User' => __('notification.admin_panel.admin_user'),
        default => class_basename($record->notifiable_type)
    };
@endphp

<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            {{ $title }}
        </h3>
        
        <div class="prose dark:prose-invert max-w-none">
            <p class="text-gray-700 dark:text-gray-300">
                {{ $body }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('notification.admin_panel.recipient') }} {{ __('notification.admin_panel.notification_details') }}</h4>
            <dl class="space-y-1">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('notification.admin_panel.recipient_type') }}:</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $notifiableType }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('notification.admin_panel.recipient') }}:</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                        @if($record->notifiable)
                            @if($record->notifiable instanceof \App\Models\Seller)
                                {{ $record->notifiable->company_name ?? $record->notifiable->name }}
                            @else
                                {{ $record->notifiable->name ?? __('notification.admin_panel.unknown') }}
                            @endif
                        @else
                            <span class="text-red-500">{{ __('notification.admin_panel.user_deleted') }}</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('notification.admin_panel.notification_details') }}</h4>
            <dl class="space-y-1">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('notification.admin_panel.status') }}:</dt>
                    <dd class="text-sm">
                        @if($record->read_at)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                {{ __('notification.admin_panel.read') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                {{ __('notification.admin_panel.unread') }}
                            </span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('notification.admin_panel.sent_at') }}:</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                        {{ $record->created_at->format('M j, Y g:i A') }}
                    </dd>
                </div>
                @if($record->read_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('notification.admin_panel.read_at') }}:</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                        {{ $record->read_at->format('M j, Y g:i A') }}
                    </dd>
                </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('notification.admin_panel.notification_type') }}:</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                        {{ class_basename($record->type) }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    @if(isset($data['args']) && !empty($data['args']))
    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('notification.admin_panel.additional_data') }}</h4>
        <pre class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ json_encode($data['args'], JSON_PRETTY_PRINT) }}</pre>
    </div>
    @endif
</div>
