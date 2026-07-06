<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Version history for a controlled document. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_document_versions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('document_id', 36);
            $table->unsignedInteger('version');
            $table->text('change_summary')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status', 20)->default('draft'); // draft/released/superseded
            $table->string('created_by', 36)->nullable();
            $table->timestamps();

            $table->index(['document_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_document_versions');
    }
};
