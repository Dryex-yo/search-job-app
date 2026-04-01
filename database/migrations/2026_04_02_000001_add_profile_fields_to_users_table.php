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
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->string('bio')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'resume_path')) {
                $table->string('resume_path')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path')->nullable()->after('resume_path');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('profile_photo_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'bio', 'resume_path', 'profile_photo_path', 'role']);
        });
    }
};
