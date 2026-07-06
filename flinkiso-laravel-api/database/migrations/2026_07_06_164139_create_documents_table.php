<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Document Control v2 governance record.
 * References the legacy CakePHP qc_documents by id when applicable.
 * Lifecycle: draft to review to approved to released to obsolete.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_documents', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('doc_number')->unique();       // e.g. SOP 001
            $table->string('title');
            $table->string('category', 60)->default('SOP'); // SOP / WI / Form / Policy / HACCP record
            $table->unsignedInteger('current_version')->default(1);
            $table->string('status', 20)->default('draft'); // draft/review/approved/released/obsolete
            $table->string('owner_id', 36)->nullable();
            $table->string('legacy_qc_document_id', 36)->nullable(); // link to CakePHP qc_documents
            $table->string('created_by', 36)->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_documents');
    }
};
