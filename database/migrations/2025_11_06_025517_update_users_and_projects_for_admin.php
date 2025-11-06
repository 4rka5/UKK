<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 30)->unique()->after('id');
            }
            if (!Schema::hasColumn('users', 'fullname')) {
                $table->string('fullname', 100)->nullable()->after('username');
            }
            if (!Schema::hasColumn('users', 'email')) {
                $table->string('email', 190)->unique()->after('fullname');
            }
            if (!Schema::hasColumn('users', 'password')) {
                $table->string('password')->after('email');
            }
            if (!Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin','team_lead','designer','developer'])
                      ->default('developer')->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('idle')->after('role');
            }
            if (!Schema::hasColumn('users', 'created_at')) {
                $table->timestamps();
            }
        });

        // Pastikan index unik ada jika kolom sudah ada sebelumnya
        try {
            if (Schema::hasColumn('users', 'email')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('email');
                });
            }
        } catch (\Throwable $e) {}
        try {
            if (Schema::hasColumn('users', 'username')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unique('username');
                });
            }
        } catch (\Throwable $e) {}

        // Projects
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'id')) {
                $table->bigIncrements('id');
            }
            if (!Schema::hasColumn('projects', 'project_name')) {
                $table->string('project_name', 150);
            }
            if (!Schema::hasColumn('projects', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('projects', 'deadline')) {
                $table->date('deadline')->nullable();
            }
            if (!Schema::hasColumn('projects', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->index();
            }
            if (!Schema::hasColumn('projects', 'created_at')) {
                $table->timestamps();
            }
        });

        // FK created_by (pisahkan agar aman di sebagian DB)
        // Cek dan drop FK lama jika ada
        $tableName = 'projects';
        $database = env('DB_DATABASE', 'ukk');

        $fks = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND CONSTRAINT_NAME LIKE '%created_by%'
        ", [$database, $tableName]);

        foreach ($fks as $fk) {
            DB::statement("ALTER TABLE {$tableName} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
        }

        // Tambah FK baru
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'created_by')) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        // Rollback minimal
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'created_by')) {
                try { $table->dropForeign(['created_by']); } catch (\Throwable $e) {}
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('projects', 'project_name')) $table->dropColumn('project_name');
            if (Schema::hasColumn('projects', 'description')) $table->dropColumn('description');
            // catatan: tidak menjatuhkan kolom id/timestamps agar aman
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) $table->dropColumn('status');
            if (Schema::hasColumn('users', 'role')) $table->dropColumn('role');
            if (Schema::hasColumn('users', 'remember_token')) $table->dropColumn('remember_token');
            if (Schema::hasColumn('users', 'password')) $table->dropColumn('password');
            if (Schema::hasColumn('users', 'email')) $table->dropUnique('users_email_unique');
            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique('users_username_unique');
                $table->dropColumn('username');
            }
        });
    }
};
