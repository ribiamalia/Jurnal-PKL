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
        Schema::table('users', function (Blueprint $table) {
            
            $table->string('image')->nullable();
            $table->string('jenis/bidang')->nullable();
            $table->string('alamat')->nullable();
            $table->string('nama_pembimbing')->nullable();
            $table->string('no_pembimbing')->nullable();
            $table->string('longitude')->nullable();
            $table->string('latitude')->nullable();
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
