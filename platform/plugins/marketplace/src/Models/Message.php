<?php

namespace Botble\Marketplace\Models;

use Botble\Base\Models\BaseModel;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends BaseModel
{
    public const SENDER_CUSTOMER = 'customer';

    public const SENDER_VENDOR = 'vendor';

    public const SENDER_GUEST = 'guest';

    protected $table = 'mp_messages';

    protected $fillable = [
        'store_id',
        'customer_id',
        'sender_type',
        'sender_id',
        'name',
        'email',
        'content',
        'read_at',
        'customer_archived_at',
        'vendor_archived_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'customer_archived_at' => 'datetime',
        'vendor_archived_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isFromCustomer(): bool
    {
        return $this->sender_type === self::SENDER_CUSTOMER;
    }

    public function isFromVendor(): bool
    {
        return $this->sender_type === self::SENDER_VENDOR;
    }

    public function isFromGuest(): bool
    {
        return $this->sender_type === self::SENDER_GUEST;
    }
}
