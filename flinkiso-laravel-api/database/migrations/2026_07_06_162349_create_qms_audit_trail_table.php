<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable, hash-chained audit trail (FDA 21 CFR Part 11).
 * Every write in the QMS engine appends one tamper-evident row here.
 * Records are append-only: no updates, no deletes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_audit_trail', function (Blueprint $table) {
            $table->bigIncrements('seq');                         // PK — strict global ordering for the hash chain
            $table->char('id', 36)->unique();                     // UUID reference

            $table->string('entity_type', 100);                   // e.g. qms_incident
            $table->string('entity_id', 36)->nullable();
            $table->string('action', 40);                         // create/update/status_change/sign

            $table->string('user_id', 36)->nullable();            // legacy CakePHP user UUID
            $table->string('username')->nullable();

            $table->json('changes')->nullable();                  // { field: {old, new} }
            $table->string('reason')->nullable();                 // reason for change
            $table->string('signature_meaning', 60)->nullable();  // reviewed/approved/authorized/performed

            $table->char('prev_hash', 64)->nullable();            // hash of previous row
            $table->char('hash', 64);                             // sha256 of this row + prev_hash

            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_audit_trail');
    }
};
