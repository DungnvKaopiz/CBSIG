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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp')->useCurrent();
            $table->unsignedTinyInteger('level')->comment('1=INFO, 2=WARNING, 3=ERROR, 4=DEBUG');
            $table->string('source', 100)->comment('Nguồn gốc log (WebCMS, STB_Player)');
            $table->text('message');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->foreignId('device_id')->nullable()->constrained('devices')->onDelete('set null')->onUpdate('cascade');
            $table->json('details_json')->nullable();

            $table->index(['level', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};



