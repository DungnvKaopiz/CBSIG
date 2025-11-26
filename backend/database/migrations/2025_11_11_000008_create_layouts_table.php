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
        Schema::create('layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('canvas_width')->default(1280)->comment('Canvas width in pixels');
            $table->unsignedInteger('canvas_height')->default(720)->comment('Canvas height in pixels');
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });

        Schema::create('layout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layout_id')->constrained('layouts')->onDelete('cascade')->onUpdate('cascade');
            $table->string('name')->comment('Frame name/identifier');
            $table->foreignId('content_id')->nullable()->constrained('contents')->onDelete('set null')->onUpdate('cascade');
            // Store frame positioning and display metadata as JSON
            // {
            //     "x": 0,
            //     "y": 0,
            //     "width": 960,
            //     "height": 540,
            //     "z_index": 1,
            //     "image_fit": 1,
            //     "order_index": 0
            // }
            $table->json('frame_metadata')->nullable()->comment('Frame positioning and display metadata');
            $table->timestamps();

            $table->index('layout_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layout_items');
        Schema::dropIfExists('layouts');
    }
};

