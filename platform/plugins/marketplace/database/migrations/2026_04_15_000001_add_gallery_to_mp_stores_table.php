<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('mp_stores', 'gallery')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->text('gallery')->nullable()->after('cover_image');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mp_stores', 'gallery')) {
            Schema::table('mp_stores', function (Blueprint $table): void {
                $table->dropColumn('gallery');
            });
        }
    }
};
