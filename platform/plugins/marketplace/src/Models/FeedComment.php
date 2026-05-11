<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Product;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedComment extends BaseModel
{
    protected $table = 'mp_feed_comments';

    protected $fillable = [
        'product_id',
        'customer_id',
        'content',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withDefault();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withDefault();
    }

    protected function content(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => trim($value)
        );
    }
}
