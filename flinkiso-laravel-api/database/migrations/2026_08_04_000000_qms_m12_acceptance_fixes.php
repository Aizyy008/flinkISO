<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Milestone 1.2 acceptance fixes.
 *
 *  - qms_user_roles.is_admin : an explicit administrator capability (decoupled
 *    from the legacy role flags) that gates the Users & Roles and Workflow-rule
 *    management screens — server-side, so unauthorized users are rejected.
 *  - qms_tasks : real, assignable tasks created by the workflow "assign a task"
 *    and "request an approval" actions (previously only a notification).
 *  - qms_evidence.original_name : the uploaded file's original name so downloads
 *    keep the correct filename and extension (e.g. .pdf).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_user_roles', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('is_publisher');
        });

        Schema::create('qms_tasks', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('kind')->default('task');          // task | approval
            $table->char('assigned_to', 36);                  // legacy users.id
            $table->string('related_type')->nullable();       // e.g. qms_incident
            $table->char('related_id', 36)->nullable();
            $table->string('status')->default('open');        // open | done
            $table->date('due_date')->nullable();
            $table->char('created_by', 36)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->char('completed_by', 36)->nullable();
            $table->timestamps();
            $table->index(['assigned_to', 'status']);
        });

        Schema::table('qms_evidence', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('qms_user_roles', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
        Schema::dropIfExists('qms_tasks');
        Schema::table('qms_evidence', function (Blueprint $table) {
            $table->dropColumn('original_name');
        });
    }
};
