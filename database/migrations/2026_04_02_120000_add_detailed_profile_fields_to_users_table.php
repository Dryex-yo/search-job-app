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
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'province')) {
                $table->string('province')->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('province');
            }
            if (!Schema::hasColumn('users', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            }
            if (!Schema::hasColumn('users', 'education_level')) {
                $table->string('education_level')->nullable()->default('SMA')->after('gender');
            }
            if (!Schema::hasColumn('users', 'education_institution')) {
                $table->string('education_institution')->nullable()->after('education_level');
            }
            if (!Schema::hasColumn('users', 'education_major')) {
                $table->string('education_major')->nullable()->after('education_institution');
            }
            if (!Schema::hasColumn('users', 'education_year_graduated')) {
                $table->integer('education_year_graduated')->nullable()->after('education_major');
            }
            if (!Schema::hasColumn('users', 'experiences')) {
                $table->json('experiences')->nullable()->after('education_year_graduated');
            }
            if (!Schema::hasColumn('users', 'skills')) {
                $table->text('skills')->nullable()->after('experiences');
            }
            if (!Schema::hasColumn('users', 'id_number')) {
                $table->string('id_number')->nullable()->unique()->after('skills');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('id_number');
            }
            if (!Schema::hasColumn('users', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
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
                'address',
                'city',
                'province',
                'postal_code',
                'date_of_birth',
                'gender',
                'education',
                'experience',
                'skills',
                'id_number',
                'emergency_contact_name',
                'emergency_contact_phone'
            ]);
            $table->dropUnique(['id_number']);
        });
    }
};
