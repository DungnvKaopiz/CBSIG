<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('analytics_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade')->onUpdate('cascade');
            $table->dateTime('timestamp')->comment('Thời điểm sự kiện xảy ra trên STB');
            $table->unsignedTinyInteger('event_type')->comment('1=face_detection, 2=body_detection');
            $table->string('age_group', 20)->nullable();
            $table->unsignedTinyInteger('gender')->nullable()->comment('1=male, 2=female, 3=unknown');
            $table->string('posture', 50)->nullable();
            $table->foreignId('current_content_id')->nullable()->constrained('contents')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('current_schedule_id')->nullable()->constrained('schedules')->onDelete('set null')->onUpdate('cascade');
            $table->json('raw_data_json')->nullable();
            $table->timestamp('created_at')->useCurrent()->comment('Thời điểm log được server nhận');

            $table->index('timestamp');
            $table->index(['age_group', 'gender']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_logs');
    }
};



