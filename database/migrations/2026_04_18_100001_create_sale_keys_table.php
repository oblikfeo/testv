<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->foreignId('key_order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->unsignedTinyInteger('panel_index')->default(0);
            $table->string('uuid');
            $table->string('email');
            $table->string('sub_id', 64)->unique();
            $table->unsignedInteger('inbound_id');

            $table->unsignedBigInteger('total_bytes')->default(0);
            $table->unsignedBigInteger('used_bytes')->default(0);

            $table->timestamp('expires_at');
            $table->timestamp('activated_at')->nullable();

            $table->boolean('is_sponsor')->default(false);

            $table->unsignedTinyInteger('secondary_panel_index')->nullable();
            $table->string('secondary_uuid')->nullable();
            $table->string('secondary_email')->nullable();
            $table->string('secondary_sub_id', 64)->nullable();
            $table->unsignedInteger('secondary_inbound_id')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();

            $table->index(['user_id', 'subscription_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_keys');
    }
};
