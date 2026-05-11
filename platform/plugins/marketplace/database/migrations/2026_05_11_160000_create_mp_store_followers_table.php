<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('mp_store_followers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('customer_id');
            $table->timestamps();

            $table->unique(['store_id', 'customer_id']);
            $table->index('customer_id');
            $table->foreign('store_id')->references('id')->on('mp_stores')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('ec_customers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_store_followers');
    }
};
