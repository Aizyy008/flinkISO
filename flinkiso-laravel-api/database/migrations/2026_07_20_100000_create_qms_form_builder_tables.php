<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drag & Drop Form Builder (FlickISO §6 / Project 1 M3).
 * Reusable forms with a full field set (incl. signature + repeatable items),
 * conditional visibility, submissions that feed records and can trigger workflows.
 * Additive; existing tables untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_forms', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category', 60)->nullable();
            $table->string('status', 20)->default('draft');           // draft | active | archived
            // Optional: a submission creates a record of this type (incident, risk, capa, …)
            $table->string('feeds_record_type', 40)->nullable();
            // Optional: a submission dispatches this workflow event.
            $table->string('trigger_event', 80)->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('qms_form_fields', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('form_id', 36);
            $table->string('field_key');                              // machine key used in submission data
            $table->string('label');
            // text|textarea|number|dropdown|multiselect|date|datetime|checkbox|radio|file|signature|repeatable|section
            $table->string('field_type', 30);
            $table->json('options')->nullable();                      // choices for dropdown/radio/multiselect; sub-fields for repeatable
            $table->boolean('required')->default(false);
            $table->string('placeholder')->nullable();
            $table->string('help_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            // Conditional visibility: show this field only when {cond_field} {cond_op} {cond_value}
            $table->string('cond_field')->nullable();
            $table->string('cond_op', 4)->nullable();                 // = | != | in
            $table->string('cond_value')->nullable();
            $table->timestamps();
            $table->index(['form_id', 'sort_order']);
        });

        Schema::create('qms_form_submissions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('reference')->unique();
            $table->char('form_id', 36);
            $table->json('data');                                     // { field_key: value, ... }
            $table->string('linked_record_type', 40)->nullable();     // record created from this submission
            $table->char('linked_record_id', 36)->nullable();
            $table->string('submitted_by')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index('form_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_form_submissions');
        Schema::dropIfExists('qms_form_fields');
        Schema::dropIfExists('qms_forms');
    }
};
