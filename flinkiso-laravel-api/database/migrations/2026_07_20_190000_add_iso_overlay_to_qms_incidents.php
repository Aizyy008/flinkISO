<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ISO overlay fields on incidents (Milestone 2.2). `iso_standard` selects which
 * standard's overlay applies; `iso_overlay` stores the standard-specific values.
 * Field definitions live in config/iso_overlays.php (no migration to add fields).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_incidents', function (Blueprint $table) {
            $table->string('iso_standard', 40)->nullable()->after('source');
            $table->json('iso_overlay')->nullable()->after('iso_standard');
        });
    }

    public function down(): void
    {
        Schema::table('qms_incidents', function (Blueprint $table) {
            $table->dropColumn(['iso_standard', 'iso_overlay']);
        });
    }
};
