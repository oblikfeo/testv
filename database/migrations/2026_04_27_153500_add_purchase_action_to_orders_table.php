<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('purchase_action', 32)
                ->default('new_purchase')
                ->after('purchase_source')
                ->index();
            $table->foreignId('target_subscription_id')
                ->nullable()
                ->after('plan_id')
                ->constrained('subscriptions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['target_subscription_id']);
            $table->dropColumn(['purchase_action', 'target_subscription_id']);
        });
    }
};
