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
        Schema::create('metric_timeseries', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key', 160)->index();
            $table->double('value')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('captured_at')->index();
            $table->timestamp('created_at')->nullable();

            $table->index(['metric_key', 'captured_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metric_timeseries');
    }
};
