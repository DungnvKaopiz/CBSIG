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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedTinyInteger('type')->comment('1=content, 2=playlist, 3=layout');
            $table->unsignedBigInteger('item_id')->comment('ID of content, playlist, or layout');

            // Time scheduling
            $table->time('start_time')->nullable()->comment('Start time (HH:mm)');
            $table->time('end_time')->nullable()->comment('End time (HH:mm)');
            $table->unsignedTinyInteger('repeat')->nullable()->default(1)->comment('1=Everyday, 2=Weekdays Only, 3=Weekends Only, 4=Custom, 5=Custom Date');
            $table->json('days_of_week')->nullable()->comment('Array of days: Mon, Tue, Wed, Thu, Fri, Sat, Sun');
            $table->json('custom_dates')->nullable()->comment('Array of custom dates (YYYY-MM-DD) for Custom Date repeat');
            
            // Status
            $table->unsignedTinyInteger('status')->default(1)->comment('1=active, 2=paused');
            
            // JSON storage for schedule configuration
            $table->json('schedule_config')->nullable()->comment('Full schedule configuration JSON');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};



