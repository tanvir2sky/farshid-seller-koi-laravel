<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('mp_stores', 'priority')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->unsignedInteger('priority')->default(0)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mp_stores', 'priority')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->dropColumn('priority');
            });
        }
    }
};
