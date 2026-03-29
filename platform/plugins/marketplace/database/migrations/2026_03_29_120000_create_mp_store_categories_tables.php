<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('mp_store_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->foreignId('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->unsigned()->default(0);
            $table->string('status', 60)->default('published');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::table('mp_store_categories', function (Blueprint $table): void {
            $table->foreign('parent_id')
                ->references('id')
                ->on('mp_store_categories')
                ->nullOnDelete();
        });

        Schema::create('mp_store_category_store', function (Blueprint $table): void {
            $table->foreignId('store_id')
                ->constrained('mp_stores')
                ->cascadeOnDelete();
            $table->foreignId('store_category_id')
                ->constrained('mp_store_categories')
                ->cascadeOnDelete();
            $table->primary(['store_id', 'store_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_store_category_store');
        Schema::dropIfExists('mp_store_categories');
    }
};
