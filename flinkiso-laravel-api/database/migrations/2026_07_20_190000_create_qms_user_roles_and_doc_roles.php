<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Role-based document workflow (client acceptance fix).
 *
 * `qms_user_roles` holds the QMS document-workflow roles (Creator / Reviewer /
 * Approver / Publisher) for each legacy user — kept in *our* database so the
 * read-only legacy `users` table is never written to. Documents gain a
 * publisher assignment and a reviewer sign-off stamp so the full
 * Creator → Reviewer → Approver → Publisher flow can be enforced and audited.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_user_roles', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('user_id', 36)->unique();   // legacy users.id (UUID)
            $table->boolean('is_creator')->default(true);
            $table->boolean('is_reviewer')->default(false);
            $table->boolean('is_approver')->default(false);
            $table->boolean('is_publisher')->default(false);
            $table->timestamps();
        });

        Schema::table('qms_documents', function (Blueprint $table) {
            $table->char('publisher_id', 36)->nullable()->after('approver_id');
            $table->char('reviewed_by', 36)->nullable()->after('publisher_id');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_user_roles');
        Schema::table('qms_documents', function (Blueprint $table) {
            $table->dropColumn(['publisher_id', 'reviewed_by', 'reviewed_at']);
        });
    }
};
