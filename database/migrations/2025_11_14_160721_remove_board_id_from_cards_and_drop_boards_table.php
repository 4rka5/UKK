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
        // Drop board_id foreign key and column from cards
        Schema::table('management_project_cards', function (Blueprint $table) {
            $table->dropForeign('cards_board_id_foreign');
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
            $table->unsignedBigInteger('board_id')->nullable()->after('project_id');
            $table->foreign('board_id', 'cards_board_id_foreign')
                  ->references('id')
                  ->on('management_project_boards')
                  ->onDelete('cascade');
        });
    }
};
