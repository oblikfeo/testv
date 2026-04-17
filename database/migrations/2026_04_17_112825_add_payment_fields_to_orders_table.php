<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('subscription_key_id')->constrained('plans')->nullOnDelete();
            $table->string('payment_id')->nullable()->after('plan_id')->index();
            $table->string('payment_status')->nullable()->after('payment_id');
            $table->integer('amount')->nullable()->after('payment_status');
            $table->string('payment_method')->nullable()->after('amount');
            $table->timestamp('paid_at')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'payment_id', 'payment_status', 'amount', 'payment_method', 'paid_at']);
        });
    }
};
