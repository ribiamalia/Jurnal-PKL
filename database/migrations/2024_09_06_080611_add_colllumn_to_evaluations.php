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
        Schema::table('evaluations', function (Blueprint $table) {
            $table ->string('disiplinWaktu')->nullable();
            $table ->string('kemampuanKerja')->nullable();
            $table ->string('kualitasKerja')->nullable();
            $table ->string('inisiatif')->nullable();
            $table ->string('perilaku')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            //
        });
    }
};
