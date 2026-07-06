<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CAPA — Corrective And Preventive Action.
 * Lifecycle: open -> in_progress -> effectiveness_check -> closed (or cancelled).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_capa', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();       // CAPA-2026-0001

            $table->string('incident_id', 36)->nullable();   // originating incident
            $table->string('type', 20)->default('corrective'); // corrective/preventive
            $table->string('title');
            $table->text('root_cause')->nullable();
            $table->text('action_plan')->nullable();

            $table->string('status', 30)->default('open');   // open/in_progress/effectiveness_check/closed/cancelled
            $table->string('priority', 20)->default('medium');

            $table->string('assigned_to', 36)->nullable();   // legacy user ref
            $table->date('due_date')->nullable();

            $table->text('effectiveness_notes')->nullable();
            $table->boolean('effectiveness_verified')->default(false);
            $table->string('verified_by', 36)->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->string('created_by', 36)->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_capa');
    }
};
