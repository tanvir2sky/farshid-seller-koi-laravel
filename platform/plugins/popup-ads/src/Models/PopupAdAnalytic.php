<?php

namespace Botble\PopupAds\Models;

use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PopupAdAnalytic extends BaseModel
{
    protected $table = 'popup_ad_analytics';

    protected $fillable = [
        'popup_ad_id',
        'date',
        'impressions',
        'clicks',
    ];

    protected $casts = [
        'date' => 'date',
        'impressions' => 'integer',
        'clicks' => 'integer',
    ];

    public function popupAd(): BelongsTo
    {
        return $this->belongsTo(PopupAd::class, 'popup_ad_id');
    }
}
