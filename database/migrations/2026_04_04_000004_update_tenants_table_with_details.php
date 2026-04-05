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
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'owner_name')) {
                $table->string('owner_name')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'owner_email')) {
                $table->string('owner_email')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'owner_phone')) {
                $table->string('owner_phone')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'industry')) {
                $table->string('industry')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'company_size')) {
                $table->string('company_size')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'country')) {
                $table->string('country')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'status')) {
                $table->string('status')->default('active');
            }
            if (!Schema::hasColumn('tenants', 'trial_ends_at')) {
                $table->dateTime('trial_ends_at')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'subscription_plan')) {
                $table->string('subscription_plan')->default('free');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'owner_name',
                'owner_email',
                'owner_phone',
                'industry',
                'company_size',
                'address',
                'city',
                'country',
                'status',
                'trial_ends_at',
                'subscription_plan',
            ]);
        });
    }
};
