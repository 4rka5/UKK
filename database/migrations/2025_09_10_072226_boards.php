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
        // Drop VIEW if it exists before creating table
        DB::statement('DROP VIEW IF EXISTS boards');
        
        Schema::create('boards', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('board_name');
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boards');
    }
};
