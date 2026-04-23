<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('popup_ad_analytics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('popup_ad_id')->constrained('popup_ads')->cascadeOnDelete();
            $table->date('date');
            $table->bigInteger('impressions')->default(0);
            $table->bigInteger('clicks')->default(0);
            $table->timestamps();

            $table->unique(['popup_ad_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_ad_analytics');
    }
};
