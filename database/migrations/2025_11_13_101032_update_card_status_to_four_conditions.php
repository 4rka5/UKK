<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Update card status enum menjadi 4 kondisi:
     * - todo: default, tugas baru dibuat
     * - in_progress: tugas sudah diserahkan ke user oleh team lead
     * - review: user sudah menyelesaikan tugas, menunggu approval
     * - done: sudah di-approve oleh team lead
     */
    public function up(): void
    {
        // Update existing data: map old status to new status
        DB::statement("UPDATE management_project_cards SET status = 'todo' WHERE status IN ('backlog', 'todo')");
        DB::statement("UPDATE management_project_cards SET status = 'review' WHERE status IN ('code_review', 'testing')");
        
        // Modify enum to 4 conditions
        DB::statement("
            ALTER TABLE management_project_cards 
            MODIFY status ENUM('todo','in_progress','review','done') DEFAULT 'todo'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke 6 kondisi
        DB::statement("
            ALTER TABLE management_project_cards 
            MODIFY status ENUM('backlog','todo','in_progress','code_review','testing','done') DEFAULT 'backlog'
        ");
        
        // Rollback data mapping
        DB::statement("UPDATE management_project_cards SET status = 'backlog' WHERE status = 'todo'");
        DB::statement("UPDATE management_project_cards SET status = 'code_review' WHERE status = 'review'");
    }
};
