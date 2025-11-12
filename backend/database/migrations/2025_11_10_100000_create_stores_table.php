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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('server_ip_addr', 255)->unique();
            $table->string('store_name', 255)->nullable();
            $table->integer('employee_number')->default(0);
            $table->integer('camera_status')->default(0)->comment('0=Enter, 1=Exit');
            $table->timestamps();
            
            $table->index('server_ip_addr', 'idx_server_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};

