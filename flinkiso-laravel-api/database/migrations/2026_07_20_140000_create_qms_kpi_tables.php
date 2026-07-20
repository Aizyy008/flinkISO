<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * KPI Engine (Project 1 M3 / FlickISO §E / Expansion §1.7):
 * KPI definitions (targets, thresholds, calculation method, aggregation) plus
 * periodic results that drive the calculated dashboard (line/bar/gauge).
 * Additive; existing tables untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_kpis', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            // quality | environment | safety | food_safety | info_security
            $table->string('area', 40)->default('quality');
            $table->string('standard', 40)->nullable();          // ISO 9001 / 22000 / 27001 …
            $table->string('unit', 40)->nullable();
            $table->string('calculation_method')->nullable();    // e.g. "on-time / total * 100"
            $table->decimal('target_value', 20, 4)->nullable();
            $table->decimal('warning_threshold', 20, 4)->nullable();
            $table->decimal('critical_threshold', 20, 4)->nullable();
            // higher_better | lower_better
            $table->string('direction', 20)->default('higher_better');
            $table->string('aggregation', 20)->default('monthly'); // monthly | quarterly | yearly
            $table->string('related_process')->nullable();
            $table->string('related_site')->nullable();
            $table->string('related_department')->nullable();
            $table->string('owner_id', 36)->nullable();
            $table->string('status', 20)->default('active');       // active | inactive
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->index(['area', 'status']);
        });

        Schema::create('qms_kpi_results', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('kpi_id', 36);
            $table->string('period_label', 40);                   // e.g. "2026-01", "Q1 2026"
            $table->date('period_date');                          // sortable period anchor
            $table->decimal('value', 20, 4);
            $table->text('notes')->nullable();
            $table->string('recorded_by')->nullable();
            $table->timestamps();
            $table->unique(['kpi_id', 'period_label']);
            $table->index(['kpi_id', 'period_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_kpi_results');
        Schema::dropIfExists('qms_kpis');
    }
};
