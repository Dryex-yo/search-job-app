<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            // Pendidikan fields
            'education_level' => ['nullable', 'string', 'max:100'],
            'education_institution' => ['nullable', 'string', 'max:255'],
            'education_year_graduated' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'education_major' => ['nullable', 'string', 'max:100'],
            // Experiences (array)
            'experiences' => ['nullable', 'array'],
            'experiences.*.company' => ['nullable', 'string', 'max:255'],
            'experiences.*.position' => ['nullable', 'string', 'max:255'],
            'experiences.*.start_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'experiences.*.end_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'experiences.*.description' => ['nullable', 'string', 'max:1000'],
            // Skills
            'skills' => ['nullable', 'string', 'max:1000'],
            'id_number' => ['nullable', 'string', 'max:50', Rule::unique(User::class)->ignore($this->user()->id)],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
