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
        Schema::table('jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('jobs', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->onDelete('cascade');
            }
            if (!Schema::hasColumn('jobs', 'recruiter_id')) {
                $table->foreignId('recruiter_id')->nullable()->after('tenant_id')->constrained('users')->onDelete('set null')->comment('Recruiter who posted this job');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeignIdFor('tenants');
            $table->dropForeignIdFor('users', 'recruiter_id');
            $table->dropColumn('tenant_id');
            $table->dropColumn('recruiter_id');
        });
    }
};
