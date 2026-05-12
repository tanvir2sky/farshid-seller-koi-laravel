<?php

namespace Botble\Marketplace\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static FeedPinTypeEnum PRODUCT()
 * @method static FeedPinTypeEnum VENDOR_STORE()
 */
class FeedPinTypeEnum extends Enum
{
    public const PRODUCT = 'product';

    public const VENDOR_STORE = 'vendor_store';

    public static $langPath = 'plugins/marketplace::feed-pin.enums.types';
}
