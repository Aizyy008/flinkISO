<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit Management module (FlickISO Upgrate §B / Project 1 M2):
 * annual audit program, scheduled audits (assigned to auditors), multi-section
 * checklist builder, and findings that feed the Incident (NC) → CAPA flow.
 * Additive; existing tables untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Annual audit program (yearly planning).
        Schema::create('qms_audit_programs', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();
            $table->unsignedSmallInteger('year');
            $table->string('title');
            $table->text('objectives')->nullable();
            $table->string('status', 20)->default('planned'); // planned | active | closed
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->index('year');
        });

        // Scheduled audits (assigned to auditors, linked to process/site/dept/clause).
        Schema::create('qms_audits', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();
            $table->char('program_id', 36)->nullable();
            $table->string('title');
            $table->string('audit_type', 20)->default('internal'); // internal | external | supplier
            $table->string('standard', 40)->nullable();            // ISO standard reference
            $table->text('scope')->nullable();
            $table->string('lead_auditor_id', 36)->nullable();
            $table->string('auditor_id', 36)->nullable();
            $table->date('planned_date')->nullable();
            $table->date('actual_date')->nullable();
            $table->string('status', 20)->default('scheduled');    // scheduled | in_progress | completed | closed
            $table->string('related_process')->nullable();
            $table->string('related_site')->nullable();
            $table->string('related_department')->nullable();
            $table->string('related_clause')->nullable();
            $table->string('related_haccp_plan')->nullable();
            $table->string('result', 20)->nullable();              // conform | minor_nc | major_nc
            $table->text('summary')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->index(['status', 'planned_date']);
            $table->index('program_id');
        });

        // Multi-section checklist items.
        Schema::create('qms_audit_checklist_items', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('audit_id', 36);
            $table->string('section')->default('General');
            $table->string('clause_ref')->nullable();
            $table->text('question');
            $table->string('response', 20)->nullable();            // conform | nonconform | observation | na
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->index('audit_id');
        });

        // Findings → link to an Incident (NC) → CAPA.
        Schema::create('qms_audit_findings', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();
            $table->char('audit_id', 36);
            $table->char('checklist_item_id', 36)->nullable();
            $table->string('finding_type', 20)->default('nonconformity'); // nonconformity | observation | ofi
            $table->string('severity', 20)->default('minor');             // minor | major | critical
            $table->text('description');
            $table->string('clause_ref')->nullable();
            $table->char('incident_id', 36)->nullable();                  // the raised NC incident
            $table->string('status', 20)->default('open');                // open | closed
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->index('audit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_audit_findings');
        Schema::dropIfExists('qms_audit_checklist_items');
        Schema::dropIfExists('qms_audits');
        Schema::dropIfExists('qms_audit_programs');
    }
};
