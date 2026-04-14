<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('mp_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id');
            $table->foreignId('customer_id')->nullable();
            $table->string('sender_type', 20)->default('customer');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('name', 60);
            $table->string('email', 60);
            $table->longText('content');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('customer_archived_at')->nullable();
            $table->timestamp('vendor_archived_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'customer_id', 'id'], 'mp_messages_store_customer_id_idx');
            $table->index(['customer_id', 'sender_type', 'read_at'], 'mp_messages_customer_sender_read_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mp_messages');
    }
};
