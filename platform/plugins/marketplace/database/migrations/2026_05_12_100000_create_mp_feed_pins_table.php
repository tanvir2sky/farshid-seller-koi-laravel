<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('mp_feed_pins', function (Blueprint $table): void {
            $table->id();
            $table->string('pin_type', 32);
            $table->unsignedBigInteger('target_id');
            $table->unsignedInteger('priority')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['is_enabled', 'starts_at', 'ends_at']);
            $table->index(['pin_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_feed_pins');
    }
};
