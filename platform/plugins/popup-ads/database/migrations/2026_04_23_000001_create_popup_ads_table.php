<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('popup_ads', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('status', 60)->default('published');
            $table->string('image')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->boolean('open_in_new_tab')->default(true);
            $table->unsignedSmallInteger('delay_seconds')->default(3);
            $table->string('dismiss_duration', 20)->default('1_day');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->integer('order')->default(0)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_ads');
    }
};
