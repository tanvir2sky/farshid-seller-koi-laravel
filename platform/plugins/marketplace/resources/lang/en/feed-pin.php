<?php

return [
    'name' => 'Feed pins',
    'create' => 'Create feed pin',
    'edit' => 'Edit feed pin',
    'priority_help' => 'Lower numbers appear first at the top of the feed.',
    'target_id_help_product' => 'Enter the published product ID to pin.',
    'target_id_help_store' => 'Enter the store (vendor) ID. Latest products from that store will be pinned.',
    'enums' => [
        'types' => [
            'product' => 'Single product',
            'vendor_store' => 'Vendor store',
        ],
        'algorithms' => [
            'follow_biased_random' => 'Random with mild boost for followed vendors',
            'follow_first_then_random' => 'Followed vendors first, then random',
            'newest' => 'Newest products first',
            'popular_by_views' => 'Most viewed first',
        ],
    ],
    'settings' => [
        'section_title' => 'Customer feed',
        'algorithm' => 'Feed ranking algorithm',
        'vendor_pin_limit' => 'Products per vendor pin',
        'vendor_pin_limit_help' => 'When pinning a vendor store, up to this many latest published products are shown.',
        'card_radius' => 'Card corner radius (px)',
        'accent_color' => 'Accent color (buttons/links)',
        'density' => 'Feed density',
        'density_normal' => 'Normal',
        'density_compact' => 'Compact',
        'show_like_counts' => 'Show like counts',
        'show_comment_counts' => 'Show comment counts',
    ],
    'form' => [
        'pin_type' => 'Pin type',
        'target_id' => 'Target ID',
        'priority' => 'Priority',
        'starts_at' => 'Starts at',
        'ends_at' => 'Ends at',
        'is_enabled' => 'Enabled',
    ],
    'table' => [
        'pin_type' => 'Type',
        'target_id' => 'Target ID',
        'priority' => 'Priority',
        'starts_at' => 'Starts at',
        'ends_at' => 'Ends at',
        'is_enabled' => 'Enabled',
    ],
];
