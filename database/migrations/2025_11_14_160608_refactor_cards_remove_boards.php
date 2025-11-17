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
            $table->unsignedBigInteger('project_id')->nullable()->after('id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
        
        // Migrate existing data: populate project_id from board relationship
        DB::statement('
            UPDATE management_project_cards c
            INNER JOIN management_project_boards b ON c.board_id = b.id
            SET c.project_id = b.project_id
            WHERE c.board_id IS NOT NULL
        ');
        
        // Make project_id required after data migration
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable(false)->change();
        });
        
        // Drop board_id foreign key and column
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->dropForeign(['board_id']);
            $table->dropColumn('board_id');
        });
        
        // Drop boards table completely
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
            $table->string('board_name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
        
        // Add board_id back to cards
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('board_id')->nullable()->after('id');
            $table->foreign('board_id')->references('id')->on('management_project_boards')->onDelete('cascade');
        });
        
        // Remove project_id from cards
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });
    }
};
