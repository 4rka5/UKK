<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek apakah tabel boards ada dan belum di-rename
        if (Schema::hasTable('boards') && !Schema::hasTable('management_project_boards')) {
            Schema::rename('boards', 'management_project_boards');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('management_project_boards') && !Schema::hasTable('boards')) {
            Schema::rename('management_project_boards', 'boards');
        }
    }
};
