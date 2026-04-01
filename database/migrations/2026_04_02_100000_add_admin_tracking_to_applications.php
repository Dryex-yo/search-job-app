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
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null')->after('user_id');
            $table->timestamp('reviewed_at')->nullable()->after('status');
            $table->text('admin_notes')->nullable()->after('reviewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('applications_admin_id_foreign');
            $table->dropColumn(['admin_id', 'reviewed_at', 'admin_notes']);
        });
    }
};
