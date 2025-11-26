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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên nội dung hoặc tiêu đề video YouTube');
            $table->unsignedTinyInteger('type')->comment('1=video, 2=image, 3=playlist, 4=youtube');
            $table->text('file_url')->comment('URL tới file trên Cloud Storage hoặc URL video YouTube');
            $table->unsignedBigInteger('file_size')->nullable()->comment('Kích thước file (bytes), là NULL cho loại youtube');
            $table->string('checksum', 64)->nullable()->comment('MD5/SHA256 của file, hoặc Video ID của YouTube');
            $table->unsignedInteger('duration_seconds')->nullable()->comment('Thời lượng phát (giây)');
            $table->string('thumbnail_url', 255)->nullable()->comment('URL ảnh thumbnail');
            $table->foreignId('uploaded_by_user_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreignId('parent_content_id')->nullable()->constrained('contents')->onDelete('set null')->onUpdate('cascade')->comment('Reference to original content when cloned with effects');
            
            // Effects metadata stored as JSON
            // Contains: rotation, text, fontFamily, fontSize, fontColor, fontWeight, textAlign, orientation,
            // letterSpacing, lineHeight, horizontalPosition, verticalPosition, startTime, endTime,
            // displayDuration, interval, scrollMode, scrollSpeed, loopCount, outlineEnabled, outlineColor,
            // outlineWidth, shadowEnabled, shadowColor, shadowBlur
            $table->json('effects_metadata')->nullable()->comment('Text overlay and effects metadata in JSON format');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};



