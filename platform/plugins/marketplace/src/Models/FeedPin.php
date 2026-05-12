<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Marketplace\Enums\FeedPinTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class FeedPin extends BaseModel
{
    protected $table = 'mp_feed_pins';

    protected $fillable = [
        'pin_type',
        'target_id',
        'priority',
        'starts_at',
        'ends_at',
        'is_enabled',
    ];

    protected $casts = [
        'pin_type' => FeedPinTypeEnum::class,
        'priority' => 'integer',
        'is_enabled' => 'bool',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query
            ->where('is_enabled', true)
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }
}
