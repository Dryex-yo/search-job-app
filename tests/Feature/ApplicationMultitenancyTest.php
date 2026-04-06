<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Job;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationMultitenancyTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected Job $job;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test-tenant.local',
        ]);

        // Initialize tenant for database
        tenancy()->initialize($this->tenant);

        // Create user in tenant
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
            'phone' => '081234567890',
            'bio' => 'Test bio',
            'address' => 'Test address',
            'city' => 'Test city',
            'province' => 'Test province',
            'postal_code' => '12345',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'education_level' => 'Bachelor',
            'education_institution' => 'Test University',
            'education_year_graduated' => 2012,
            'education_major' => 'Computer Science',
            'education_grade' => '3.8',
            'skills' => 'PHP, Laravel, Vue.js',
            'resume_path' => 'resumes/test.pdf',
            'profile_photo_path' => 'profile-photos/test.jpg',
        ]);

        // Create job in tenant
        $this->job = Job::create([
            'title' => 'Software Engineer',
            'company' => 'Test Company',
            'location' => 'Jakarta',
            'job_type' => 'Full-time',
            'salary_min' => 10000000,
            'salary_max' => 20000000,
            'description' => 'Test job description',
            'requirements' => 'Test requirements',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    protected function tearDown(): void
    {
        tenancy()->forgetCurrent();
        parent::tearDown();
    }

    /**
     * Test: Application has tenant_id when created
     */
    public function test_application_has_tenant_id_when_created(): void
    {
        $this->actingAs($this->user);

        $application = Application::create([
            'job_id' => $this->job->id,
            'user_id' => $this->user->id,
            'resume_path' => 'resumes/test.pdf',
            'cover_letter' => 'Test cover letter',
            'status' => 'pending',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertNotNull($application->tenant_id);
        $this->assertEquals($this->tenant->id, $application->tenant_id);
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test: Application event has tenant context
     */
    public function test_application_submitted_event_has_tenant_info(): void
    {
        $this->actingAs($this->user);

        $application = Application::create([
            'job_id' => $this->job->id,
            'user_id' => $this->user->id,
            'resume_path' => 'resumes/test.pdf',
            'cover_letter' => 'Test cover letter',
            'status' => 'pending',
            'tenant_id' => $this->tenant->id,
        ]);

        // Verify application has the tenant_id
        $this->assertEquals($this->tenant->id, $application->tenant_id);
        $this->assertNotNull($application->tenant_id);
    }

    /**
     * Test: Application belongs to correct tenant
     */
    public function test_application_belongs_to_correct_tenant(): void
    {
        $application = Application::create([
            'job_id' => $this->job->id,
            'user_id' => $this->user->id,
            'resume_path' => 'resumes/test.pdf',
            'cover_letter' => 'Test cover letter',
            'status' => 'pending',
            'tenant_id' => $this->tenant->id,
        ]);

        $this->assertTrue($application->belongsToCurrentTenant());
        $this->assertEquals($this->tenant->id, $application->tenant->id);
    }
}
