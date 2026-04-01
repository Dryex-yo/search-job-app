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
            // Tambah/ubah field pendidikan untuk lebih terstruktur
            if (!Schema::hasColumn('users', 'education_level')) {
                $table->string('education_level')->nullable()->default('SMA')->after('education');
            }
            if (!Schema::hasColumn('users', 'education_institution')) {
                $table->string('education_institution')->nullable()->after('education_level');
            }
            if (!Schema::hasColumn('users', 'education_year_graduated')) {
                $table->integer('education_year_graduated')->nullable()->after('education_institution');
            }
            if (!Schema::hasColumn('users', 'education_major')) {
                $table->string('education_major')->nullable()->after('education_year_graduated');
            }
            
            // Ubah experience menjadi JSON untuk multiple entries
            if (Schema::hasColumn('users', 'experience')) {
                if (!Schema::hasColumn('users', 'experiences')) {
                    $table->json('experiences')->nullable()->after('education_major');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'education_level',
                'education_institution', 
                'education_year_graduated',
                'education_major'
            ]);
            $table->text('experiences')->nullable()->change();
        });
    }
};
