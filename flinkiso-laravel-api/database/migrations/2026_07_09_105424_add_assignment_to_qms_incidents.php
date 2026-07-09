<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qms_incidents', function (Blueprint $table) {
            $table->string('assigned_to', 36)->nullable()->after('detected_by');
            $table->date('due_date')->nullable()->after('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('qms_incidents', function (Blueprint $table) {
            $table->dropColumn(['assigned_to', 'due_date']);
        });
    }
};
