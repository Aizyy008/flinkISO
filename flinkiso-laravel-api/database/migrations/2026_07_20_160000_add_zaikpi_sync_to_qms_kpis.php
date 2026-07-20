<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Outbound ZaiKPI sync state on each KPI. Additive; lets the KPI screen show
 * whether a definition is mirrored in ZaiKPI and surfaces the last error.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_kpis', function (Blueprint $table) {
            $table->timestamp('zaikpi_synced_at')->nullable();
            $table->string('zaikpi_status', 20)->nullable(); // synced | failed
            $table->string('zaikpi_error', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('qms_kpis', function (Blueprint $table) {
            $table->dropColumn(['zaikpi_synced_at', 'zaikpi_status', 'zaikpi_error']);
        });
    }
};
