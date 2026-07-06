<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Change Request workflow for controlled documents. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_change_requests', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();        // CR 2026 0001
            $table->string('document_id', 36);
            $table->text('reason');
            $table->string('status', 20)->default('open'); // open/approved/rejected/implemented
            $table->string('requested_by', 36)->nullable();
            $table->string('decided_by', 36)->nullable();
            $table->timestamps();

            $table->index(['document_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_change_requests');
    }
};
