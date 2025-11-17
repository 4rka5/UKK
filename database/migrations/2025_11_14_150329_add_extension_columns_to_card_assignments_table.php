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
        Schema::table('card_assignments', function (Blueprint $table) {
            $table->boolean('extension_requested')->default(false)->after('is_working');
            $table->text('extension_reason')->nullable()->after('extension_requested');
            $table->timestamp('extension_requested_at')->nullable()->after('extension_reason');
            $table->boolean('extension_approved')->nullable()->after('extension_requested_at');
            $table->unsignedBigInteger('extension_approved_by')->nullable()->after('extension_approved');
            $table->timestamp('extension_approved_at')->nullable()->after('extension_approved_by');
            
            $table->foreign('extension_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('card_assignments', function (Blueprint $table) {
            $table->dropForeign(['extension_approved_by']);
            $table->dropColumn([
                'extension_requested',
                'extension_reason',
                'extension_requested_at',
                'extension_approved',
                'extension_approved_by',
                'extension_approved_at'
            ]);
        });
    }
};
