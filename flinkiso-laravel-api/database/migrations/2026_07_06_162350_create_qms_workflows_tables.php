<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reusable JSON workflow engine.
 * qms_workflows      = definitions (trigger + conditions + actions).
 * qms_workflow_runs  = execution log of each fired workflow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_workflows', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('name');
            $table->string('trigger_event', 80);         // incident.created / risk.high / capa.overdue
            $table->json('conditions')->nullable();      // [{field, op, value}]
            $table->json('actions');                     // [{type, params}]
            $table->boolean('active')->default(true);
            $table->string('created_by', 36)->nullable();
            $table->timestamps();

            $table->index('trigger_event');
        });

        Schema::create('qms_workflow_runs', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('workflow_id', 36);
            $table->string('trigger_event', 80);
            $table->string('entity_type', 100)->nullable();
            $table->string('entity_id', 36)->nullable();
            $table->string('status', 20)->default('completed'); // completed/failed/skipped
            $table->json('result')->nullable();          // actions executed + outcome
            $table->text('error')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['workflow_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_workflow_runs');
        Schema::dropIfExists('qms_workflows');
    }
};
