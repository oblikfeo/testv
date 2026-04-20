<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trial_key_id')->constrained('trial_keys')->cascadeOnDelete();
            $table->string('hwid')->index();
            $table->string('name')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->unique(['trial_key_id', 'hwid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_devices');
    }
};
