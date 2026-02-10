<?php

return [
    'configuration' => [
        'general' => [
            'title' => 'General',
            'subtitle' => 'Manage your site settings',
        ],
        'notification' => [
            'title' => 'Notifications',
            'subtitle' => 'Manage your site notifications',
            'contact_email' => 'Contact email',
            'push' => [
                'title' => 'Manual push',
                'subtitle' => 'Send push notifications to active tenant devices.',
                'target' => 'Target',
                'target_all' => 'All active devices',
                'target_device' => 'Single device',
                'device' => 'Device',
                'device_placeholder' => 'Select a device',
                'device_required' => 'You must select a device.',
                'device_not_found' => 'No active devices were found for the selected target.',
                'active_devices' => 'Active devices: :count',
                'no_devices' => 'There are no active devices registered for this tenant yet.',
                'title_label' => 'Title',
                'message' => 'Message',
                'send' => 'Send push',
                'disabled' => 'Push sending is disabled. Enable EXPO_PUSH_ENABLED to use this feature.',
                'tenant_not_available' => 'Could not resolve the current tenant for this notification.',
                'sent_success' => 'Push sent to :sent devices.',
                'sent_partial' => 'Push sent to :sent devices. Failures: :errors.',
                'send_failed' => 'The push notification could not be delivered to the selected target.',
            ],
        ],
        'appearance' => [
            'logo' => 'Logo',
            'cover' => 'Portada (imagen de fondo)',
            'favicon' => 'Favicon',
        ],
    ],
    'landing' => [
        'banners' => [
            'desktop_image' => 'Desktop image',
            'mobile_image' => 'Mobile image',
        ],
    ],

];
