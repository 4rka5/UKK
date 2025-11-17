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
        // Add project_id to cards table
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('id')->constrained('projects')->onDelete('cascade');
        });

        // Copy project_id from boards to cards
        DB::statement('
            UPDATE management_project_cards c
            INNER JOIN management_project_boards b ON c.board_id = b.id
            SET c.project_id = b.project_id
        ');

        // Make project_id required
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable(false)->change();
        });

        // Drop board_id from cards (no foreign key to drop since it wasn't defined)
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->dropColumn('board_id');
        });

        // Drop boards table
        Schema::dropIfExists('management_project_boards');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate boards table
        Schema::create('management_project_boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('board_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Add board_id back to cards
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->foreignId('board_id')->nullable()->after('project_id')->constrained('management_project_boards')->onDelete('cascade');
        });

        // Create default board for each project and link cards
        DB::statement('
            INSERT INTO management_project_boards (project_id, board_name, description, created_at, updated_at)
            SELECT DISTINCT project_id, "Main Board", "Default board", NOW(), NOW()
            FROM management_project_cards
        ');

        DB::statement('
            UPDATE management_project_cards c
            INNER JOIN management_project_boards b ON c.project_id = b.project_id
            SET c.board_id = b.id
        ');

        // Remove project_id from cards
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }

};
