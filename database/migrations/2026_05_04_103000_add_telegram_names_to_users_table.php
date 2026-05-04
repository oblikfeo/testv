<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Реальные имена из Telegram (`message.from_user.first_name/last_name`).
            // Нужны для отображения юзеров без публичного @username — у них telegram_username
            // приходит NULL, а админка раньше показывала «—». См. AdminController/test-keys.blade.php.
            $table->string('telegram_first_name', 64)->nullable()->after('telegram_username');
            $table->string('telegram_last_name', 64)->nullable()->after('telegram_first_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['telegram_first_name', 'telegram_last_name']);
        });
    }
};
