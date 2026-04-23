<?php

return [
    [
        'name' => 'Popup Ads',
        'flag' => 'popup-ads.index',
    ],
    [
        'name'        => 'Create',
        'flag'        => 'popup-ads.create',
        'parent_flag' => 'popup-ads.index',
    ],
    [
        'name'        => 'Edit',
        'flag'        => 'popup-ads.edit',
        'parent_flag' => 'popup-ads.index',
    ],
    [
        'name'        => 'Delete',
        'flag'        => 'popup-ads.destroy',
        'parent_flag' => 'popup-ads.index',
    ],
    [
        'name'        => 'Analytics',
        'flag'        => 'popup-ads.analytics',
        'parent_flag' => 'popup-ads.index',
    ],
];
