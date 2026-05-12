<?php

namespace Botble\Marketplace\Enums;

use Botble\Base\Supports\Enum;

/**
 * @method static FeedAlgorithmEnum FOLLOW_BIASED_RANDOM()
 * @method static FeedAlgorithmEnum FOLLOW_FIRST_THEN_RANDOM()
 * @method static FeedAlgorithmEnum NEWEST()
 * @method static FeedAlgorithmEnum POPULAR_BY_VIEWS()
 */
class FeedAlgorithmEnum extends Enum
{
    public const FOLLOW_BIASED_RANDOM = 'follow_biased_random';

    public const FOLLOW_FIRST_THEN_RANDOM = 'follow_first_then_random';

    public const NEWEST = 'newest';

    public const POPULAR_BY_VIEWS = 'popular_by_views';

    public static $langPath = 'plugins/marketplace::feed-pin.enums.algorithms';
}
