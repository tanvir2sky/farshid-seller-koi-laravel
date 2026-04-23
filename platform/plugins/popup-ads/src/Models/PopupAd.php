<?php

namespace Botble\PopupAds\Models;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\Media\Facades\RvMedia;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PopupAd extends BaseModel
{
    protected $table = 'popup_ads';

    protected $fillable = [
        'name',
        'status',
        'image',
        'title',
        'description',
        'url',
        'open_in_new_tab',
        'delay_seconds',
        'dismiss_duration',
        'started_at',
        'ended_at',
        'order',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'open_in_new_tab' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->wherePublished()
            ->where(function (Builder $q): void {
                $q->whereNull('started_at')->orWhere('started_at', '<=', Carbon::now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('ended_at')->orWhere('ended_at', '>=', Carbon::now());
            });
    }

    public function analyticsData(): HasMany
    {
        return $this->hasMany(PopupAdAnalytic::class, 'popup_ad_id');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => RvMedia::getImageUrl($this->image));
    }

    public static function getDismissDurationOptions(): array
    {
        return [
            'session'  => trans('plugins/popup-ads::popup-ads.dismiss.session'),
            '1_day'    => trans('plugins/popup-ads::popup-ads.dismiss.1_day'),
            '7_days'   => trans('plugins/popup-ads::popup-ads.dismiss.7_days'),
            '30_days'  => trans('plugins/popup-ads::popup-ads.dismiss.30_days'),
            'forever'  => trans('plugins/popup-ads::popup-ads.dismiss.forever'),
        ];
    }
}
