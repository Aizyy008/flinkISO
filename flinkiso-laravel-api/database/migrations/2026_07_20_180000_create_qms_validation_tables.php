<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GMP / Validation logging (Milestone 2.2). Records equipment (IQ/OQ/PQ),
 * process, cleaning, computer-system and method validations with outcome,
 * approval status and a revalidation (valid-until) date. Additive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_validations', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();
            // equipment_iq | equipment_oq | equipment_pq | process | cleaning | computer_system | method | other
            $table->string('type', 40)->default('equipment_oq');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('subject')->nullable();            // what is being validated (asset/process name)
            $table->char('asset_id', 36)->nullable();         // optional link to qms_assets
            $table->string('protocol_no')->nullable();
            $table->date('performed_date')->nullable();
            $table->string('performed_by')->nullable();
            $table->string('result', 20)->nullable();         // pass | fail | conditional
            $table->string('status', 20)->default('planned'); // planned | in_progress | approved | rejected | expired
            $table->date('valid_until')->nullable();          // revalidation due date
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_validations');
    }
};
