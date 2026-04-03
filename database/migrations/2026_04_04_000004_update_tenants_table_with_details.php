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
            $table->string('owner_name')->after('database')->nullable();
            $table->string('owner_email')->after('owner_name')->nullable();
            $table->string('owner_phone')->after('owner_email')->nullable();
            $table->string('industry')->after('owner_phone')->nullable();
            $table->string('company_size')->after('industry')->nullable();
            $table->text('address')->after('company_size')->nullable();
            $table->string('city')->after('address')->nullable();
            $table->string('country')->after('city')->nullable();
            $table->string('status')->default('active')->after('country');
            $table->dateTime('trial_ends_at')->nullable()->after('status');
            $table->string('subscription_plan')->default('free')->after('trial_ends_at');
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
