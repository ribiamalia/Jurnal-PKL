<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('nis');
            $table->string('placeOfBirth');
            $table->date('dateOfBirth');
            $table->string('gender');
            $table->string('bloodType');
            $table->text('alamat');
            $table->string('image');
            $table->foreignId('class_id')->references('id')->on('classes')->cascadeOnDelete();
            $table->foreignId('industri_id')->references('id')->on('industries')->cascadeOnDelete();
            $table->foreignId('departemen_id')->references('id')->on('departemens')->cascadeOnDelete();
            $table->foreignId('parent_id')->references('id')->on('parents')->cascadeOnDelete();
            $table->foreignId('teacher_id')->references('id')->on('teachers')->cascadeOnDelete();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
