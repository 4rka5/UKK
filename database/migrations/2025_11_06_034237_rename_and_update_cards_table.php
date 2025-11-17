<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek apakah tabel cards masih ada (belum di-rename)
        if (Schema::hasTable('cards') && !Schema::hasTable('management_project_cards')) {
            // Rename tabel cards ke management_project_cards
            Schema::rename('cards', 'management_project_cards');

            // Update struktur tabel
            Schema::table('management_project_cards', function (Blueprint $table) {
                // Tambah kolom description baru jika belum ada
                if (!Schema::hasColumn('management_project_cards', 'description')) {
                    $table->text('description')->nullable()->after('card_title');
                }
            });

            // Copy data dari deskripsi ke description jika kolom deskripsi ada and description exists
            try {
                if (Schema::hasColumn('management_project_cards', 'deskripsi') && Schema::hasColumn('management_project_cards', 'description')) {
                    DB::statement("UPDATE management_project_cards SET description = deskripsi");
                }
            } catch (\Throwable $e) {
                // ignore
            }

            // Drop kolom deskripsi lama jika ada
            Schema::table('management_project_cards', function (Blueprint $table) {
                if (Schema::hasColumn('management_project_cards', 'deskripsi')) {
                    $table->dropColumn('deskripsi');
                }
            });
        }

        // Update struktur kolom (berlaku untuk tabel yang sudah ada atau baru di-rename)
        if (Schema::hasTable('management_project_cards')) {
            // Set description nullable
            DB::statement("ALTER TABLE management_project_cards MODIFY description TEXT NULL");

            // Update struktur kolom lainnya
            DB::statement("ALTER TABLE management_project_cards
                MODIFY estimated_hours DECIMAL(8,2) NULL,
                MODIFY actual_hours DECIMAL(8,2) NULL,
                MODIFY due_date DATE NULL");

            // Update status: ubah 'review' menjadi 'code_review' jika ada
            DB::statement("UPDATE management_project_cards SET status = 'code_review' WHERE status = 'review'");

            // Set enum baru untuk status dengan 6 pilihan
            DB::statement("ALTER TABLE management_project_cards
                MODIFY status ENUM('backlog','todo','in_progress','code_review','testing','done') DEFAULT 'backlog'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan enum status
        DB::statement("ALTER TABLE management_project_cards
            MODIFY status ENUM('todo','in_progress','review','done') DEFAULT 'todo'");

        // Update status kembali
        DB::statement("UPDATE management_project_cards SET status = 'review' WHERE status = 'code_review'");

        // Tambah kolom deskripsi
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->text('deskripsi')->after('card_title');
        });

        // Copy data kembali
        DB::statement("UPDATE management_project_cards SET deskripsi = description");

        // Drop kolom description
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        // Kembalikan struktur kolom
        DB::statement("ALTER TABLE management_project_cards
            MODIFY estimated_hours DECIMAL(8,2) NOT NULL,
            MODIFY actual_hours DECIMAL(8,2) NOT NULL,
            MODIFY due_date DATE NOT NULL");

        // Rename kembali ke cards
        Schema::rename('management_project_cards', 'cards');
    }
};
