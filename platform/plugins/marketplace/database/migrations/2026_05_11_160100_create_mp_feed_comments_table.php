<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('mp_feed_comments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('customer_id');
            $table->text('content');
            $table->timestamps();

            $table->index('product_id');
            $table->index('customer_id');
            $table->foreign('product_id')->references('id')->on('ec_products')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('ec_customers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_feed_comments');
    }
};
