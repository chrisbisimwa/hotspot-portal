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
         Schema::table('hotspot_users', function (Blueprint $table) {
            if (!Schema::hasColumn('hotspot_users', 'batch_ref')) {
                $table->string('batch_ref', 60)->nullable()->index()->after('owner_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotspot_users', function (Blueprint $table) {
            if (Schema::hasColumn('hotspot_users', 'batch_ref')) {
                $table->dropColumn('batch_ref');
            }
        });
    }
};
