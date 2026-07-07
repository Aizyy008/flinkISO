<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ISO document control fields (per the QMS Expansion spec, section 1.3):
 * document type, issue/revision numbers, effective + review dates,
 * reviewer/approver assignment, and related standard/clause/process/site/department.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_documents', function (Blueprint $table) {
            $table->string('document_type', 60)->nullable()->after('category');
            $table->unsignedInteger('issue_number')->default(1)->after('current_version');
            $table->unsignedInteger('revision_number')->default(0)->after('issue_number');
            $table->date('effective_date')->nullable()->after('revision_number');
            $table->date('review_due_date')->nullable()->after('effective_date');
            $table->string('reviewer_id', 36)->nullable()->after('owner_id');
            $table->string('approver_id', 36)->nullable()->after('reviewer_id');
            $table->string('related_standard_id', 36)->nullable()->after('approver_id');
            $table->string('related_clause_id', 36)->nullable()->after('related_standard_id');
            $table->string('related_process')->nullable()->after('related_clause_id');
            $table->string('related_site')->nullable()->after('related_process');
            $table->string('related_department')->nullable()->after('related_site');
        });
    }

    public function down(): void
    {
        Schema::table('qms_documents', function (Blueprint $table) {
            $table->dropColumn([
                'document_type', 'issue_number', 'revision_number', 'effective_date',
                'review_due_date', 'reviewer_id', 'approver_id', 'related_standard_id',
                'related_clause_id', 'related_process', 'related_site', 'related_department',
            ]);
        });
    }
};
