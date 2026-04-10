<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pairs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('panel_base_url');
            $table->text('panel_username');
            $table->text('panel_password');
            $table->unsignedBigInteger('inbound_id')->nullable();
            $table->string('remark_prefix')->nullable();
            $table->unsignedInteger('batch_size')->default(50);
            $table->unsignedInteger('refill_threshold')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pairs');
    }
};
