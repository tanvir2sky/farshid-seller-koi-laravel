<?php

return [
    'name'   => 'Popup Ads',
    'create' => 'New Popup Ad',
    'edit'   => 'Edit Popup Ad',

    'fields' => [
        'title'                 => 'Overlay Title',
        'title_helper'          => 'Optional headline displayed over the image inside the popup.',
        'description'           => 'Overlay Description',
        'url'                   => 'Destination URL',
        'open_in_new_tab'       => 'Open link in new tab?',
        'delay_seconds'         => 'Show after (seconds)',
        'delay_seconds_helper'  => 'Number of seconds after page load before the popup appears.',
        'dismiss_duration'      => 'Hide popup after close for',
        'started_at'            => 'Active from',
        'started_at_helper'     => 'Leave empty to activate immediately.',
        'ended_at'              => 'Active until',
        'ended_at_helper'       => 'Leave empty for no end date.',
    ],

    'dismiss' => [
        'session'  => 'This session only',
        '1_day'    => '1 day',
        '7_days'   => '7 days',
        '30_days'  => '30 days',
        'forever'  => 'Forever (do not show again)',
    ],

    'analytics' => [
        'title'              => 'Analytics — :name',
        'impressions_today'  => 'Impressions Today',
        'clicks_today'       => 'Clicks Today',
        'impressions'        => 'Impressions',
        'clicks'             => 'Clicks',
        'ctr'                => 'CTR',
        'total_impressions'  => 'Total Impressions',
        'total_clicks'       => 'Total Clicks',
        'last_days'          => 'Last :days days',
        'no_data'            => 'No analytics data for this period.',
    ],
];
