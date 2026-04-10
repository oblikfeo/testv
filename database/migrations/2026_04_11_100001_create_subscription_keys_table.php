<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pair_id')->constrained('pairs')->cascadeOnDelete();
            $table->string('status', 32)->index();
            $table->text('connection_url');
            $table->string('panel_client_id')->nullable()->index();
            $table->json('panel_raw')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_in_panel_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_keys');
    }
};
