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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_uid')->unique()->comment('Mã định danh duy nhất của thiết bị');
            $table->string('name')->comment('Tên thân thiện của thiết bị');
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('status')->default(5)->comment('1=online, 2=offline, 3=syncing, 4=error, 5=pending');
            $table->timestamp('last_seen_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('api_key')->unique();
            $table->string('firmware_version', 50)->nullable();
            $table->unsignedInteger('canvas_width')->nullable()->default(1280)->comment('Canvas width in pixels');
            $table->unsignedInteger('canvas_height')->nullable()->default(720)->comment('Canvas height in pixels');
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};



