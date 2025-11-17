<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify enum to include 'active' value
        if (Schema::hasTable('users')) {
            // MySQL: alter column to new enum values
            DB::statement("ALTER TABLE `users` MODIFY `status` ENUM('idle','working','active') NOT NULL DEFAULT 'idle'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            DB::statement("ALTER TABLE `users` MODIFY `status` ENUM('idle','working') NOT NULL DEFAULT 'idle'");
        }
    }
};
