<?php

return [
    'navigation' => [
        'model' => 'Announcement',
        'plural' => 'Announcements',
    ],

    'form' => [
        'title' => [
            'label' => 'Title',
            'helper' => 'Short headline shown on the dashboard.',
        ],
        'body' => [
            'label' => 'Body',
            'helper' => 'Full message users should read.',
        ],
        'type' => [
            'label' => 'Type',
            'helper' => 'Severity drives icon and colors on the dashboard.',
        ],
        'is_active' => [
            'label' => 'Active',
            'helper' => 'Inactive announcements are hidden from everyone.',
        ],
        'is_dismissible' => [
            'label' => 'Dismissible',
            'helper' => 'If off, users cannot dismiss the banner.',
        ],
        'starts_at' => [
            'label' => 'Starts at',
            'helper' => 'Optional. Hidden until this moment.',
        ],
        'expires_at' => [
            'label' => 'Expires at',
            'helper' => 'Optional. Hidden automatically after this moment.',
        ],
    ],

    'table' => [
        'created_at' => 'Created at',
        'column_active' => 'Active',
        'expired' => 'Expired',
        'filter_inactive_manual' => 'Inactive (manual)',
        'filter_expired_by_date' => 'Expired by date',
        'filter_scheduled_future' => 'Scheduled (future start)',
        'bulk_activate' => 'Activate selected',
        'bulk_deactivate' => 'Deactivate selected',
    ],
];
