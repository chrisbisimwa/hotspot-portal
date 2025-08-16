<?php

declare(strict_types=1);

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
        Schema::create('sla_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('metric_key')->index(); // ex: 'mikrotik.ping_ms','payment.initiate_latency_ms','provisioning.error_rate'
            $table->double('value');
            $table->timestamp('captured_at')->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sla_metrics');
    }
};