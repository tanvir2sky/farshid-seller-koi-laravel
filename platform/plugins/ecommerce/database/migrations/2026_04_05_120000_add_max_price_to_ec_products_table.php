<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('ec_products', 'max_price')) {
            Schema::table('ec_products', function (Blueprint $table): void {
                $table->double('max_price')->unsigned()->nullable()->after('price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ec_products', 'max_price')) {
            Schema::table('ec_products', function (Blueprint $table): void {
                $table->dropColumn('max_price');
            });
        }
    }
};
