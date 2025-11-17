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
        Schema::table('management_project_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('management_project_cards', 'board_id')) {
                $table->unsignedBigInteger('board_id')->nullable()->after('project_id');
                $table->foreign('board_id')->references('id')->on('boards')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('management_project_cards', function (Blueprint $table) {
            if (Schema::hasColumn('management_project_cards', 'board_id')) {
                $table->dropForeign(['board_id']);
                $table->dropColumn('board_id');
            }
        });
    }
};
