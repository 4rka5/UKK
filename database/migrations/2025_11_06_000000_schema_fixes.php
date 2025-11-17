<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users: tambah role, unique, remember token
        try {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'role')) {
                    $table->enum('role', ['admin','team_lead','designer','developer'])->default('developer')->after('email');
                }
                // add unique indexes only if they don't exist
                try {
                    $table->unique('email');
                } catch (\Throwable $e) {
                    // ignore if unique exists
                }
                try {
                    $table->unique('username');
                } catch (\Throwable $e) {
                    // ignore if unique exists
                }
                if (!Schema::hasColumn('users', 'remember_token')) {
                    $table->rememberToken();
                }
            });
        } catch (\Throwable $e) {
            // Ignore errors (column/index may already exist)
        }

        // projects: jadikan created_by FK
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });
        try {
            Schema::table('projects', function (Blueprint $table) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // ignore if foreign already exists
        }

        // boards: FK ke projects
        try {
            Schema::table('boards', function (Blueprint $table) {
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
                $table->text('description')->nullable()->change();
            });
        } catch (\Throwable $e) {
            // ignore if foreign already exists
        }

        // cards: FK, rename kolom, precision decimal, default
        Schema::table('cards', function (Blueprint $table) {
            // rename deskripsi -> description bila kolom ada
            if (Schema::hasColumn('cards', 'deskripsi') && !Schema::hasColumn('cards', 'description')) {
                $table->renameColumn('deskripsi', 'description');
            }
        });
        try {
            Schema::table('cards', function (Blueprint $table) {
                $table->foreign('board_id')->references('id')->on('boards')->onDelete('cascade');
                $table->unsignedBigInteger('created_by')->nullable()->change();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->date('due_date')->nullable()->change();
                $table->enum('priority', ['low','medium','high'])->default('medium')->change();
                $table->decimal('estimated_hours', 6, 2)->nullable()->change();
                $table->decimal('actual_hours', 6, 2)->default(0)->change();
            });
        } catch (\Throwable $e) {
            // ignore if foreign already exists or column changes conflict
        }

        // subtasks: precision decimal
        Schema::table('subtasks', function (Blueprint $table) {
            $table->decimal('estimated_hours', 6, 2)->nullable()->change();
        });

        // comments: buat FK, izinkan nullable sesuai type
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('subtask_id')->nullable()->change();
            $table->unsignedBigInteger('card_id')->change();
            $table->unsignedBigInteger('user_id')->change();
        });
        try {
            Schema::table('comments', function (Blueprint $table) {
                $table->foreign('subtask_id')->references('id')->on('subtasks')->onDelete('cascade');
                $table->foreign('card_id')->references('id')->on('cards')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // ignore if foreign already exists
        }

        // project_members: rename kolom + FK + unique
        Schema::table('project_members', function (Blueprint $table) {
            if (Schema::hasColumn('project_members', 'projects_id') && !Schema::hasColumn('project_members', 'project_id')) {
                $table->renameColumn('projects_id', 'project_id');
            }
            if (Schema::hasColumn('project_members', 'users_id') && !Schema::hasColumn('project_members', 'user_id')) {
                $table->renameColumn('users_id', 'user_id');
            }
        });
        try {
            Schema::table('project_members', function (Blueprint $table) {
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['project_id','user_id']);
            });
        } catch (\Throwable $e) {
            // Ignore foreign key/constraint errors if they already exist on some environments
        }

        // card_assignments: default assigned_at, timestamps
        Schema::table('card_assignments', function (Blueprint $table) {
            $table->timestamp('assigned_at')->useCurrent()->change();
            $table->timestamps();
            $table->unique(['card_id','user_id']);
        });

        // time_logs: timestamps
        Schema::table('time_logs', function (Blueprint $table) {
            $table->timestamps();
            $table->index(['card_id','subtask_id','user_id']);
        });
    }

    public function down(): void
    {
        // biarkan kosong atau tulis rollback minimal (opsional)
    }
};
