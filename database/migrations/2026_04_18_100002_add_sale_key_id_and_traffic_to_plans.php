<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('sale_key_id')->nullable()->after('subscription_key_id')->constrained('sale_keys')->nullOnDelete();
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('traffic_gb')->default(0)->after('sort_order')->comment('0 = без лимита');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['sale_key_id']);
            $table->dropColumn('sale_key_id');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('traffic_gb');
        });
    }
};
