<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_counters', function (Blueprint $table) {
            $table->string('key', 64)->primary();
            $table->unsignedBigInteger('value')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_counters');
    }
};
