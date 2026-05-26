<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('sub_id', 32)->unique();
            $table->unsignedBigInteger('total_bytes')->default(0);
            $table->unsignedBigInteger('used_bytes')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('trial_used')->default(false)->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_keys');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('trial_used');
        });
    }
};
