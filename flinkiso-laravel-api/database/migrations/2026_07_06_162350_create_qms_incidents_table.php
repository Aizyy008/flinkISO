<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Incidents / Non-conformities / Deviations.
 * Feeds CAPA. References legacy master data (company/site/user) by UUID.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_incidents', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();       // human code e.g. INC-2026-0001

            $table->string('type', 40)->default('non_conformity'); // non_conformity/deviation/incident/complaint/near_miss
            $table->string('title');
            $table->text('description')->nullable();

            $table->string('severity', 20)->default('low');   // low/medium/high/critical
            $table->string('source', 60)->nullable();         // audit/customer/internal/ccp ...
            $table->text('root_cause')->nullable();
            $table->text('containment_action')->nullable();

            $table->string('status', 20)->default('open');    // open/investigating/capa_raised/closed
            $table->string('risk_id', 36)->nullable();        // originating risk (optional)

            $table->string('company_id', 36)->nullable();     // legacy ref
            $table->string('detected_by', 36)->nullable();    // legacy user ref
            $table->date('detected_date')->nullable();

            $table->string('created_by', 36)->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_incidents');
    }
};
