<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The two remaining KPI variables from the client's "Each KPI must have the
 * variables" list: measurement frequency and data source. Additive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_kpis', function (Blueprint $table) {
            $table->string('frequency', 40)->nullable()->after('aggregation');   // how often measured
            $table->string('data_source')->nullable()->after('frequency');       // where the value comes from
        });
    }

    public function down(): void
    {
        Schema::table('qms_kpis', function (Blueprint $table) {
            $table->dropColumn(['frequency', 'data_source']);
        });
    }
};
