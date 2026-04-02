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
            // Change the role enum to include 'recruiter'
            $table->enum('role', ['user', 'admin', 'recruiter'])->default('user')->change();
        });
    }

    /**
     * Run the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert to original enum without 'recruiter'
            $table->enum('role', ['user', 'admin'])->default('user')->change();
        });
    }
};
