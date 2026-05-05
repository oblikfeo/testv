<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('plans')->where('slug', 'standard-90')->update(['traffic_gb' => 300]);
        DB::table('plans')->where('slug', 'standard-180')->update(['traffic_gb' => 600]);
        DB::table('plans')->where('slug', 'extended-90')->update(['traffic_gb' => 300]);
        DB::table('plans')->where('slug', 'extended-180')->update(['traffic_gb' => 600]);
    }

    public function down(): void
    {
        DB::table('plans')->where('slug', 'standard-90')->update(['traffic_gb' => 100]);
        DB::table('plans')->where('slug', 'standard-180')->update(['traffic_gb' => 100]);
        DB::table('plans')->where('slug', 'extended-90')->update(['traffic_gb' => 100]);
        DB::table('plans')->where('slug', 'extended-180')->update(['traffic_gb' => 100]);
    }
};
