<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * In-app notifications (email delivery handled separately by the notifier).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qms_notifications', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('user_id', 36);               // legacy CakePHP user UUID recipient
            $table->string('type', 60)->default('info'); // info/approval/deviation/capa/kpi_alert
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('related_type', 100)->nullable();
            $table->string('related_id', 36)->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('emailed')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qms_notifications');
    }
};
