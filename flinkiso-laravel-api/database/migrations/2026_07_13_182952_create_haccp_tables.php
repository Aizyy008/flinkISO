<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HACCP / Food Safety (ISO 22000).
 * Plan -> process steps -> hazard analysis -> CCPs -> CCP monitoring logs.
 * A CCP log outside its critical limit is a deviation, which auto raises a CAPA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_haccp_plans', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();
            $table->string('product');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft/approved/active/obsolete
            $table->text('team')->nullable();
            $table->string('approved_by', 36)->nullable();
            $table->date('approved_date')->nullable();
            $table->string('created_by', 36)->nullable();
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('qms_haccp_steps', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('plan_id', 36);
            $table->unsignedInteger('seq')->default(1);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index(['plan_id', 'seq']);
        });

        Schema::create('qms_haccp_hazards', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('plan_id', 36);
            $table->string('step_id', 36)->nullable();
            $table->string('hazard_type', 20); // biological/chemical/physical/allergen
            $table->text('description')->nullable();
            $table->string('significance', 20)->default('medium'); // low/medium/high
            $table->text('control_measure')->nullable();
            $table->string('control_type', 10)->default('PRP'); // PRP/OPRP/CCP
            $table->timestamps();
            $table->index(['plan_id', 'control_type']);
        });

        Schema::create('qms_haccp_ccps', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('plan_id', 36);
            $table->string('step_id', 36)->nullable();
            $table->string('hazard_id', 36)->nullable();
            $table->string('name');
            $table->string('critical_limit');            // e.g. "72C for 15 sec"
            $table->decimal('limit_min', 12, 4)->nullable(); // numeric bounds for auto deviation check
            $table->decimal('limit_max', 12, 4)->nullable();
            $table->string('monitor_what')->nullable();   // Temperature
            $table->string('monitor_how')->nullable();
            $table->string('monitor_frequency')->nullable();
            $table->string('responsible')->nullable();
            $table->text('corrective_action')->nullable();
            $table->timestamps();
            $table->index('plan_id');
        });

        Schema::create('qms_haccp_ccp_logs', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('ccp_id', 36);
            $table->string('plan_id', 36);
            $table->string('batch_no')->nullable();
            $table->decimal('measured_value', 12, 4)->nullable(); // Temperature
            $table->string('measured_time')->nullable();          // Time / hold duration (e.g. 15 sec)
            $table->timestamp('measured_at')->useCurrent();
            $table->string('operator_id', 36)->nullable();        // Operator
            $table->boolean('within_limit')->default(true);
            $table->string('result', 20)->default('ok'); // ok/deviation
            $table->string('capa_id', 36)->nullable();    // set when a deviation raises a CAPA
            $table->text('notes')->nullable();
            $table->string('logged_by', 36)->nullable();
            $table->timestamps();
            $table->index(['ccp_id', 'result']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_haccp_ccp_logs');
        Schema::dropIfExists('qms_haccp_ccps');
        Schema::dropIfExists('qms_haccp_hazards');
        Schema::dropIfExists('qms_haccp_steps');
        Schema::dropIfExists('qms_haccp_plans');
    }
};
