<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Risk register supporting all ISO standards.
 * risk_score = likelihood * severity * detection (computed in the model).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_risks', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();       // RISK-2026-0001

            $table->string('standard', 20)->nullable();  // 9001/14001/22000/27001/13485 ...
            $table->string('context', 60)->nullable();   // process/product/facility/it_asset/ccp/equipment
            $table->string('hazard_type', 60)->nullable(); // environmental/food_safety/info_security/medical
            $table->string('title');
            $table->text('description')->nullable();

            $table->unsignedTinyInteger('likelihood')->default(1); // 1-5
            $table->unsignedTinyInteger('severity')->default(1);   // 1-5
            $table->unsignedTinyInteger('detection')->default(1);  // 1-5
            $table->unsignedInteger('risk_score')->default(1);     // computed
            $table->string('risk_level', 20)->default('low');      // low/medium/high/critical

            $table->text('treatment_plan')->nullable();
            $table->string('status', 20)->default('open');  // open/mitigated/accepted/closed
            $table->string('owner_id', 36)->nullable();     // legacy user ref
            $table->string('company_id', 36)->nullable();

            $table->string('created_by', 36)->nullable();
            $table->timestamps();

            $table->index(['standard', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_risks');
    }
};
