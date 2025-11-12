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
        Schema::create('display_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->integer('display_orientation')->default(2)->comment('1=Portrait, 2=Landscape');
            $table->integer('display_angle')->default(0)->comment('0=0°, 1=90°, 2=180°, 3=270°');
            $table->integer('effect')->default(0)->comment('0=None, 1=Fade, 2=Right-Left, 3=Left-Right, 4=Upper-Bottom, 5=Bottom-Upper');
            $table->integer('effect_duration')->default(1)->comment('seconds');
            $table->integer('still_image_show_duration_minutes')->default(0);
            $table->integer('still_image_show_duration_seconds')->default(10);
            $table->integer('html_show_duration_minutes')->default(0);
            $table->integer('html_show_duration_seconds')->default(10);
            $table->integer('start_position_x')->default(0);
            $table->integer('start_position_y')->default(0);
            $table->integer('resolution_x')->default(0);
            $table->integer('resolution_y')->default(0);
            $table->timestamps();
            
            $table->unique('store_id', 'unique_store');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_settings');
    }
};

