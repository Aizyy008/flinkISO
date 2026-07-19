<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Electronic signature records (FDA 21 CFR Part 11 §11.50/§11.200). Each signing
 * of a controlled document action captures the signer, the meaning of the
 * signature, the reason, the exact timestamp, and a reference to the precise
 * record/action signed — authenticated at the moment of signing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_electronic_signatures', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('entity_type', 60)->default('qms_document');
            $table->char('entity_id', 36);                 // the document id
            $table->unsignedInteger('document_version')->nullable();
            $table->string('action', 60);                  // reviewed | approved | released | change_request_approved | obsoleted
            $table->string('meaning', 60);                 // reviewed | approved | authorized | ...
            $table->string('reason')->nullable();
            $table->string('signer_id', 36);
            $table->string('signer_name');
            $table->string('signer_username')->nullable();
            $table->string('record_reference');            // e.g. qms_document:{id}:v{n}:{action}
            $table->unsignedBigInteger('audit_seq')->nullable(); // links to qms_audit_trail.seq
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('signed_at');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_electronic_signatures');
    }
};
