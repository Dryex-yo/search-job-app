<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Applicants
 * 
 * APIs for managing job applicants and their applications
 */
class ApplicantsController extends Controller
{
    /**
     * List all applicants
     *
     * Get a paginated list of all applicants in the system with their
     * application status, job details, and submission information.
     *
     * @queryParam page integer The page number for pagination. Defaults to 1. Example: 1
     * @queryParam per_page integer Number of records per page. Defaults to 15. Example: 15
     * @queryParam status string Filter by application status. Example: pending
     * @queryParam job_id integer Filter by job ID. Example: 1
     * @queryParam sort_by string Sort field (created_at, updated_at, status). Example: created_at
     * @queryParam sort_order string Sort order (asc, desc). Example: desc
     *
     * @response 200 scenario="Success" {
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 5,
     *       "job_id": 2,
     *       "status": "pending",
     *       "applied_at": "2024-01-15T10:30:00Z",
     *       "updated_at": "2024-01-15T10:30:00Z",
     *       "user": {
     *         "id": 5,
     *         "name": "John Doe",
     *         "email": "john@example.com",
     *         "phone": "+628123456789"
     *       },
     *       "job": {
     *         "id": 2,
     *         "title": "Laravel Developer",
     *         "company": "Tech Company",
     *         "location": "Jakarta"
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "per_page": 15,
     *     "total": 50,
     *     "last_page": 4
     *   },
     *   "links": {
     *     "first": "http://api.example.com/v1/applicants?page=1",
     *     "last": "http://api.example.com/v1/applicants?page=4",
     *     "next": "http://api.example.com/v1/applicants?page=2"
     *   }
     * }
     *
     * @response 401 scenario="Unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     *
     * @authenticated
     */
    public function index(Request $request): JsonResponse
    {
        $query = Application::query()
            ->with(['user', 'job'])
            ->orderBy($request->input('sort_by', 'created_at'), $request->input('sort_order', 'desc'));

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by job
        if ($request->has('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }

        $applicants = $query->paginate($request->input('per_page', 15));

        return response()->json($applicants);
    }

    /**
     * Get applicant details
     *
     * Retrieve detailed information about a specific applicant including
     * their profile, resume, cover letter, and application timeline.
     *
     * @urlParam id integer required The applicant/application ID. Example: 1
     *
     * @response 200 scenario="Success" {
     *   "data": {
     *     "id": 1,
     *     "user_id": 5,
     *     "job_id": 2,
     *     "status": "pending",
     *     "cover_letter": "I am interested in this position...",
     *     "applied_at": "2024-01-15T10:30:00Z",
     *     "updated_at": "2024-01-15T10:30:00Z",
     *     "user": {
     *       "id": 5,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "phone": "+628123456789",
     *       "city": "Jakarta",
     *       "country": "Indonesia",
     *       "cv_url": "https://storage.example.com/cv.pdf"
     *     },
     *     "job": {
     *       "id": 2,
     *       "title": "Laravel Developer",
     *       "description": "We are looking for...",
     *       "company": "Tech Company",
     *       "location": "Jakarta",
     *       "salary_min": 10000000,
     *       "salary_max": 15000000
     *     },
     *     "timeline": [
     *       {
     *         "event": "Application submitted",
     *         "timestamp": "2024-01-15T10:30:00Z"
     *       },
     *       {
     *         "event": "Status changed to pending",
     *         "timestamp": "2024-01-15T10:30:00Z"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 404 scenario="Not found" {
     *   "message": "Applicant not found"
     * }
     *
     * @response 401 scenario="Unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     *
     * @authenticated
     */
    public function show(int $id): JsonResponse
    {
        $applicant = Application::with(['user', 'job'])
            ->findOrFail($id);

        return response()->json([
            'data' => $applicant,
        ]);
    }

    /**
     * Update applicant status
     *
     * Update the application status or add notes to an applicant's record.
     * Status can be: pending, shortlisted, rejected, accepted, or interviewed.
     *
     * @urlParam id integer required The applicant/application ID. Example: 1
     *
     * @bodyParam status string The new application status. Example: shortlisted
     * @bodyParam notes string Additional notes about the applicant. Example: Good communication skills
     *
     * @response 200 scenario="Success" {
     *   "data": {
     *     "id": 1,
     *     "user_id": 5,
     *     "job_id": 2,
     *     "status": "shortlisted",
     *     "updated_at": "2024-01-16T14:20:00Z"
     *   },
     *   "message": "Applicant updated successfully"
     * }
     *
     * @response 422 scenario="Validation error" {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "status": ["The status field must be one of: pending, shortlisted, rejected, accepted, interviewed"]
     *   }
     * }
     *
     * @response 404 scenario="Not found" {
     *   "message": "Applicant not found"
     * }
     *
     * @authenticated
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $applicant = Application::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,shortlisted,rejected,accepted,interviewed',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $applicant->update($validated);

        return response()->json([
            'data' => $applicant,
            'message' => 'Applicant updated successfully',
        ]);
    }

    /**
     * Delete applicant
     *
     * Remove an applicant record from the system. This action cannot be undone.
     *
     * @urlParam id integer required The applicant/application ID. Example: 1
     *
     * @response 204 scenario="Success (No Content)"
     *
     * @response 404 scenario="Not found" {
     *   "message": "Applicant not found"
     * }
     *
     * @authenticated
     */
    public function destroy(int $id): JsonResponse
    {
        $applicant = Application::findOrFail($id);
        $applicant->delete();

        return response()->json(null, 204);
    }
}
