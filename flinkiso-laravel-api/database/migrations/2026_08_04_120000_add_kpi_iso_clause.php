<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ISO clause on a KPI (e.g. "9.1.1") so the clause is transferred to ZaiKPI
 * alongside the ISO standard — per the agreed transfer set (standards & clauses).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_kpis', function (Blueprint $table) {
            $table->string('clause', 40)->nullable()->after('standard');
        });
    }

    public function down(): void
    {
        Schema::table('qms_kpis', function (Blueprint $table) {
            $table->dropColumn('clause');
        });
    }
};
