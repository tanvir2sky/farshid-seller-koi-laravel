<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreFollower extends BaseModel
{
    protected $table = 'mp_store_followers';

    protected $fillable = [
        'store_id',
        'customer_id',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class)->withDefault();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withDefault();
    }
}
