<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shared evidence store — attachments/records/measurements linked to any QMS entity.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_evidence', function (Blueprint $table) {
            $table->char('id', 36)->primary();

            $table->string('related_type', 100);        // qms_incident / qms_capa / qms_risk ...
            $table->string('related_id', 36);
            $table->string('evidence_type', 40)->default('file'); // file/photo/measurement/record/report

            $table->string('title')->nullable();
            $table->string('file_path')->nullable();
            $table->json('json_data')->nullable();       // structured measurement/record data

            $table->string('created_by', 36)->nullable();
            $table->timestamp('record_date')->useCurrent();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_evidence');
    }
};
