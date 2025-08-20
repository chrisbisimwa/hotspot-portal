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
         Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'rate_limit')) {
                $table->string('rate_limit', 100)->nullable()->after('mikrotik_profile');
            }
            if (!Schema::hasColumn('user_profiles', 'session_timeout')) {
                $table->string('session_timeout', 50)->nullable()->after('rate_limit');
            }
            if (!Schema::hasColumn('user_profiles', 'idle_timeout')) {
                $table->string('idle_timeout', 50)->nullable()->after('session_timeout');
            }
            if (!Schema::hasColumn('user_profiles', 'keepalive_timeout')) {
                $table->string('keepalive_timeout', 50)->nullable()->after('idle_timeout');
            }
            if (!Schema::hasColumn('user_profiles', 'shared_users')) {
                $table->unsignedInteger('shared_users')->nullable()->after('keepalive_timeout');
            }
            if (!Schema::hasColumn('user_profiles', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('user_profiles', 'sync_error')) {
                $table->string('sync_error', 255)->nullable()->after('synced_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $cols = [
                'rate_limit','session_timeout','idle_timeout',
                'keepalive_timeout','shared_users','synced_at','sync_error'
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('user_profiles', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
