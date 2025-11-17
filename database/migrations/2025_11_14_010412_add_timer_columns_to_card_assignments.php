<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan kolom untuk tracking waktu kerja:
     * - work_started_at: waktu mulai kerjakan
     * - work_paused_at: waktu pause (jika ada)
     * - total_work_seconds: total detik bekerja (accumulated)
     * - is_working: flag apakah sedang bekerja
     */
    public function up(): void
    {
        Schema::table('card_assignments', function (Blueprint $table) {
            $table->timestamp('work_started_at')->nullable()->after('assigned_at');
            $table->timestamp('work_paused_at')->nullable()->after('work_started_at');
            $table->integer('total_work_seconds')->default(0)->after('work_paused_at');
            $table->boolean('is_working')->default(false)->after('total_work_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_assignments', function (Blueprint $table) {
            $table->dropColumn(['work_started_at', 'work_paused_at', 'total_work_seconds', 'is_working']);
        });
    }
};
