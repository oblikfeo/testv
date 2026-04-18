<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_keys', function (Blueprint $table) {
            $table->boolean('is_admin_bundle')->default(false)->after('is_sponsor');
            $table->boolean('admin_primary_is_test')->default(false)->after('is_admin_bundle');
            $table->json('bundle_endpoints')->nullable()->after('secondary_inbound_id');
        });
    }

    public function down(): void
    {
        Schema::table('sale_keys', function (Blueprint $table) {
            $table->dropColumn(['is_admin_bundle', 'admin_primary_is_test', 'bundle_endpoints']);
        });
    }
};
