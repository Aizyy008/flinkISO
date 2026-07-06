<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Tracking of printed/controlled copies of released documents. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_controlled_copies', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('document_id', 36);
            $table->unsignedInteger('version');
            $table->string('holder');                     // person/department holding the copy
            $table->string('location')->nullable();
            $table->timestamp('issued_at')->useCurrent();
            $table->timestamp('returned_at')->nullable();
            $table->string('issued_by', 36)->nullable();
            $table->timestamps();

            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_controlled_copies');
    }
};
