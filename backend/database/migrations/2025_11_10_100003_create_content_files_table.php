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
        Schema::create('content_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade')->comment('NULL = global content');
            $table->string('filename', 255);
            $table->string('file_type', 50)->comment('video, image, html, pdf, json');
            $table->string('file_path', 500);
            $table->bigInteger('file_size')->default(0);
            $table->integer('duration_seconds')->nullable()->comment('For videos');
            $table->integer('width')->nullable()->comment('For images/videos');
            $table->integer('height')->nullable()->comment('For images/videos');
            $table->timestamps();
            
            $table->index('store_id', 'idx_store');
            $table->index('file_type', 'idx_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_files');
    }
};

