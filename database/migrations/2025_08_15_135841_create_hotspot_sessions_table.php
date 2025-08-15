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
        Schema::create('hotspot_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotspot_user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('start_time');
            $table->timestamp('stop_time')->nullable();
            $table->integer('session_time')->nullable(); // seconds
            $table->bigInteger('upload_mb')->nullable();
            $table->bigInteger('download_mb')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('interface')->nullable();
            $table->string('mikrotik_session_id')->nullable();
            $table->timestamps();
            
            $table->index('hotspot_user_id');
            $table->index('interface');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot_sessions');
    }
};
