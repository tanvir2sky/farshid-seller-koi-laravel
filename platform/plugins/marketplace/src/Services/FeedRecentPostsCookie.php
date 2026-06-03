<?php

namespace Botble\Marketplace\Services;

use Botble\Marketplace\Facades\MarketplaceHelper;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class FeedRecentPostsCookie
{
    public const NAME = 'feed_my_recent_posts';

    public const MAX_ENTRIES = 20;

    /**
     * @return list<int> Product IDs, newest first.
     */
    public static function activeProductIds(?Request $request = null): array
    {
        $request = $request ?: request();
        $entries = self::parseEntries((string) $request->cookie(self::NAME, ''));

        return array_values(array_map(
            fn (array $entry) => (int) $entry['id'],
            $entries
        ));
    }

    public static function makeCookieForProduct(int $productId, ?Request $request = null): Cookie
    {
        $request = $request ?: request();
        $entries = self::parseEntries((string) $request->cookie(self::NAME, ''));

        $entries = array_values(array_filter(
            $entries,
            fn (array $entry) => (int) $entry['id'] !== $productId
        ));

        array_unshift($entries, [
            'id' => $productId,
            't' => time(),
        ]);

        $entries = self::pruneEntries($entries);

        return cookie(
            self::NAME,
            json_encode($entries),
            self::cookieLifetimeMinutes(),
            '/',
            null,
            (bool) config('session.secure', false),
            true,
            false,
            config('session.same_site', 'lax')
        );
    }

    /**
     * @return list<array{id: int, t: int}>
     */
    protected static function parseEntries(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return [];
        }

        $cutoff = time() - (self::highlightDays() * 86400);
        $entries = [];

        foreach ($decoded as $entry) {
            if (! is_array($entry) || ! isset($entry['id'], $entry['t'])) {
                continue;
            }

            $id = (int) $entry['id'];
            $timestamp = (int) $entry['t'];

            if ($id <= 0 || $timestamp < $cutoff) {
                continue;
            }

            $entries[] = ['id' => $id, 't' => $timestamp];
        }

        usort($entries, fn (array $a, array $b) => $b['t'] <=> $a['t']);

        return array_slice($entries, 0, self::MAX_ENTRIES);
    }

    /**
     * @param  list<array{id: int, t: int}>  $entries
     * @return list<array{id: int, t: int}>
     */
    protected static function pruneEntries(array $entries): array
    {
        $cutoff = time() - (self::highlightDays() * 86400);

        $entries = array_values(array_filter(
            $entries,
            fn (array $entry) => (int) ($entry['t'] ?? 0) >= $cutoff
        ));

        usort($entries, fn (array $a, array $b) => $b['t'] <=> $a['t']);

        return array_slice($entries, 0, self::MAX_ENTRIES);
    }

    protected static function highlightDays(): int
    {
        return max(1, (int) MarketplaceHelper::getSetting('feed_recent_post_highlight_days', 7));
    }

    protected static function cookieLifetimeMinutes(): int
    {
        return self::highlightDays() * 24 * 60;
    }
}
