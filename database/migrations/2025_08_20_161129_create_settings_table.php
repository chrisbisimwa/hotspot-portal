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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();                       // ex: provisioning.username_prefix
            $table->string('group')->index();                      // ex: provisioning
            $table->string('type')->default('string');             // string | int | bool | json | array | float
            $table->json('value')->nullable();                     // stocké normalisé (même pour types simples)
            $table->json('meta')->nullable();                      // label, description, constraints
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
