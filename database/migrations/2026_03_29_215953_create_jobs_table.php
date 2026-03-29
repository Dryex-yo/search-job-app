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
        Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('title'); // Contoh: Fullstack Developer
                $table->string('company_name'); // Contoh: Devoryn Tech
                $table->string('location'); // Contoh: Jakarta / Remote
                $table->string('salary'); // Contoh: Rp 10.000.000
                $table->text('description');
                $table->string('type'); // Full-time, Contract, dll
                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
