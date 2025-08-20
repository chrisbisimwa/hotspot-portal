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
        Schema::table('logs', function (Blueprint $table) {
            // Ajoute des index si pas déjà présents (utiliser try/catch si besoin)
            $table->index('actor_id', 'logs_actor_id_index');
            $table->index('created_at', 'logs_created_at_index');
            $table->index(['loggable_type','loggable_id'], 'logs_loggable_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex('logs_actor_id_index');
            $table->dropIndex('logs_created_at_index');
            $table->dropIndex('logs_loggable_index');
        });
    }
};
