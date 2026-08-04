<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Approval tasks record an explicit decision (approved / rejected) rather than a
 * generic "done" — so "Request an approval" tasks carry an Approve/Reject outcome.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_tasks', function (Blueprint $table) {
            $table->string('outcome')->nullable()->after('status');   // approved | rejected
        });
    }

    public function down(): void
    {
        Schema::table('qms_tasks', function (Blueprint $table) {
            $table->dropColumn('outcome');
        });
    }
};
