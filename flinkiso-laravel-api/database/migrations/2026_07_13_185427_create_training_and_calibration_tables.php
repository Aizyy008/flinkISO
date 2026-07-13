<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Training & Competency (ISO 9001/45001/13485) and Asset & Calibration (ISO 17025).
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Training & Competency ---
        Schema::create('qms_trainings', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category', 60)->nullable();
            $table->unsignedInteger('validity_months')->nullable(); // retraining cycle; null = no expiry
            $table->boolean('mandatory')->default(false);
            $table->string('created_by', 36)->nullable();
            $table->timestamps();
        });

        Schema::create('qms_training_records', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('training_id', 36);
            $table->string('user_id', 36);                 // legacy employee/user
            $table->string('status', 20)->default('assigned'); // assigned/completed/expired
            $table->date('completed_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('result')->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by', 36)->nullable();
            $table->timestamps();
            $table->index(['training_id', 'user_id']);
            $table->index('status');
        });

        // --- Asset & Calibration ---
        Schema::create('qms_assets', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();
            $table->string('name');
            $table->string('asset_type', 60)->nullable();
            $table->string('location')->nullable();
            $table->string('serial_no')->nullable();
            $table->boolean('requires_calibration')->default(true);
            $table->unsignedInteger('calibration_frequency_months')->nullable();
            $table->date('next_due_date')->nullable();
            $table->string('status', 20)->default('active'); // active/inactive
            $table->string('created_by', 36)->nullable();
            $table->timestamps();
            $table->index('next_due_date');
        });

        Schema::create('qms_calibrations', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('asset_id', 36);
            $table->date('performed_date');
            $table->string('result', 20)->default('pass'); // pass/fail
            $table->string('performed_by')->nullable();
            $table->date('next_due_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by', 36)->nullable();
            $table->timestamps();
            $table->index('asset_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_calibrations');
        Schema::dropIfExists('qms_assets');
        Schema::dropIfExists('qms_training_records');
        Schema::dropIfExists('qms_trainings');
    }
};
