<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create a compatibility view named `cards` that maps to `management_project_cards`
        // This keeps legacy code that queries `cards` working without renaming tables again.
        DB::statement("CREATE OR REPLACE VIEW `cards` AS SELECT * FROM `management_project_cards`");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS `cards`");
    }
};
