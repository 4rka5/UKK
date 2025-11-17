<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                // add user_id if missing (some app code stores user_id on session)
                if (!Schema::hasColumn('sessions', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->after('last_activity');
                }
                if (!Schema::hasColumn('sessions', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('sessions', 'user_agent')) {
                    $table->text('user_agent')->nullable()->after('ip_address');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                if (Schema::hasColumn('sessions', 'user_agent')) {
                    $table->dropColumn('user_agent');
                }
                if (Schema::hasColumn('sessions', 'ip_address')) {
                    $table->dropColumn('ip_address');
                }
                if (Schema::hasColumn('sessions', 'user_id')) {
                    $table->dropColumn('user_id');
                }
            });
        }
    }
};
