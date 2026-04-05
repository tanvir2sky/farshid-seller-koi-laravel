<?php

namespace Botble\Marketplace\Supports;

class VendorImpersonation
{
    public const SESSION_KEY = 'marketplace_admin_impersonating_vendor';

    public static function isActive(): bool
    {
        return session()->has(self::SESSION_KEY);
    }

    /**
     * @return array{store_id: int|string, store_name: string, admin_id: int|string}|null
     */
    public static function payload(): ?array
    {
        $data = session()->get(self::SESSION_KEY);

        return is_array($data) ? $data : null;
    }

    public static function start(int|string $storeId, string $storeName, int|string $adminId): void
    {
        session()->put(self::SESSION_KEY, [
            'store_id' => $storeId,
            'store_name' => $storeName,
            'admin_id' => $adminId,
        ]);
    }

    public static function stop(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
