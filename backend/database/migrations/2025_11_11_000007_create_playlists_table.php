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
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('restrict')->onUpdate('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('playlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained('playlists')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();

            $table->index(['playlist_id', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_items');
        Schema::dropIfExists('playlists');
    }
};

