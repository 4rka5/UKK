<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create a compatibility view named `boards` that maps to `management_project_boards`
        DB::statement("CREATE OR REPLACE VIEW `boards` AS SELECT * FROM `management_project_boards`");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `boards`");
    }
};
