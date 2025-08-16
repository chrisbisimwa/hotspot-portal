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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status', 30)->index(); // open, monitoring, mitigated, resolved, false_positive
            $table->string('severity', 20)->index(); // critical, high, medium, low
            $table->timestamp('started_at');
            $table->timestamp('detected_at')->nullable();
            $table->timestamp('mitigated_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('detection_source')->nullable(); // ex: 'alert:payment_failure_rate'
            $table->text('summary')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('impact')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};