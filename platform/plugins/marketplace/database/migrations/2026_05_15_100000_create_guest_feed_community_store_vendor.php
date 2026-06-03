<?php

use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Enums\StoreStatusEnum;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Models\VendorInfo;
use Botble\Setting\Facades\Setting;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    public const STORE_ID = 9999;

    public const CUSTOMER_EMAIL = 'feed-community-vendor@system.invalid';

    public const STORE_SLUG = 'community-feed-store';

    public function up(): void
    {
        if (! Schema::hasTable('mp_stores') || ! Schema::hasTable('ec_customers')) {
            return;
        }

        if (Store::query()->whereKey(self::STORE_ID)->exists()) {
            $this->ensureGuestFeedStoreSetting();

            return;
        }

        $now = now();

        $customer = Customer::query()->where('email', self::CUSTOMER_EMAIL)->first();

        if (! $customer) {
            $customerId = DB::table('ec_customers')->insertGetId([
                'name' => 'Community Feed Vendor',
                'email' => self::CUSTOMER_EMAIL,
                'password' => Hash::make(Str::random(48)),
                'avatar' => null,
                'dob' => null,
                'phone' => null,
                'remember_token' => null,
                'status' => CustomerStatusEnum::ACTIVATED,
                'confirmed_at' => $now,
                'is_vendor' => true,
                'vendor_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $customer = Customer::query()->findOrFail($customerId);
        } else {
            DB::table('ec_customers')
                ->where('id', $customer->getKey())
                ->update([
                    'is_vendor' => true,
                    'vendor_verified_at' => $customer->vendor_verified_at ?? $now,
                    'confirmed_at' => $customer->confirmed_at ?? $now,
                    'status' => CustomerStatusEnum::ACTIVATED,
                    'updated_at' => $now,
                ]);

            $customer->refresh();
        }

        if (
            Schema::hasTable('mp_vendor_info')
            && ! VendorInfo::query()->where('customer_id', $customer->getKey())->exists()
        ) {
            VendorInfo::query()->create([
                'customer_id' => $customer->getKey(),
                'balance' => 0,
                'total_fee' => 0,
                'total_revenue' => 0,
            ]);
        }

        $storePayload = [
            'id' => self::STORE_ID,
            'name' => 'Community Feed Store',
            'email' => self::CUSTOMER_EMAIL,
            'phone' => null,
            'address' => null,
            'country' => null,
            'state' => null,
            'city' => null,
            'customer_id' => $customer->getKey(),
            'logo' => null,
            'description' => 'Default marketplace store for guest-submitted feed products.',
            'content' => null,
            'status' => StoreStatusEnum::PUBLISHED,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('mp_stores', 'company')) {
            $storePayload['company'] = null;
        }

        if (Schema::hasColumn('mp_stores', 'zip_code')) {
            $storePayload['zip_code'] = null;
        }

        if (Schema::hasColumn('mp_stores', 'logo_square')) {
            $storePayload['logo_square'] = null;
        }

        if (Schema::hasColumn('mp_stores', 'cover_image')) {
            $storePayload['cover_image'] = null;
        }

        if (Schema::hasColumn('mp_stores', 'priority')) {
            $storePayload['priority'] = 0;
        }

        DB::table('mp_stores')->insert($storePayload);

        $this->syncStoreAutoIncrement();

        if (Schema::hasTable('slugs')) {
            $exists = Slug::query()
                ->where('reference_type', Store::class)
                ->where('reference_id', self::STORE_ID)
                ->exists();

            if (! $exists) {
                Slug::query()->create([
                    'reference_type' => Store::class,
                    'reference_id' => self::STORE_ID,
                    'key' => self::STORE_SLUG,
                    'prefix' => SlugHelper::getPrefix(Store::class, 'stores'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->ensureGuestFeedStoreSetting();
    }

    public function down(): void
    {
        if (! Schema::hasTable('mp_stores')) {
            return;
        }

        $store = Store::query()->find(self::STORE_ID);

        if (! $store || $store->email !== self::CUSTOMER_EMAIL) {
            return;
        }

        if (Schema::hasTable('slugs')) {
            Slug::query()
                ->where('reference_type', Store::class)
                ->where('reference_id', self::STORE_ID)
                ->delete();
        }

        DB::table('mp_stores')->where('id', self::STORE_ID)->delete();

        $this->syncStoreAutoIncrement();

        $customer = Customer::query()->where('email', self::CUSTOMER_EMAIL)->first();

        if ($customer) {
            if (Schema::hasTable('mp_vendor_info')) {
                VendorInfo::query()->where('customer_id', $customer->getKey())->delete();
            }

            $customer->delete();
        }
    }

    protected function ensureGuestFeedStoreSetting(): void
    {
        if (! class_exists(Setting::class) || ! Schema::hasTable('settings')) {
            return;
        }

        Setting::set('marketplace_feed_guest_post_store_id', self::STORE_ID);
    }

    protected function syncStoreAutoIncrement(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $maxId = (int) DB::table('mp_stores')->max('id');

        DB::statement('ALTER TABLE mp_stores AUTO_INCREMENT = ' . ($maxId + 1));
    }
};
