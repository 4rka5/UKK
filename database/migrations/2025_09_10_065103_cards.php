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
        // Drop view if exists (created by later migration)
        DB::statement('DROP VIEW IF EXISTS cards');
        
        Schema::create('cards', function(Blueprint $table){
            $table->id();
            $table->unsignedBigInteger( 'board_id');
            $table->string('card_title');
            $table->text('deskripsi');
            $table->integer('created_by');
            $table->timestamps();
            $table->date('due_date');
            $table->enum('status', ['todo','in_progress','review','done'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high']);
            $table->decimal('estimated_hours');
            $table->decimal('actual_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
