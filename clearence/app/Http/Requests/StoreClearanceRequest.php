<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route is already guarded by auth + role:student middleware.
        return true;
    }

    public function rules(): array
    {
        return [
            'academic_year'  => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'       => ['required', 'in:First,Second'],
            'clearance_type' => ['required', 'in:graduation,semester,withdrawal,transfer'],
            'reason'         => ['nullable', 'string', 'max:500'],

            // Up to 5 files; each PDF/JPG/PNG and max 5 MB.
            'files'          => ['nullable', 'array', 'max:5'],
            'files.*'        => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],

            'declaration'    => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'declaration.required' => 'You must confirm the declaration before submitting.',
            'declaration.accepted' => 'You must confirm the declaration before submitting.',
            'files.max'            => 'You may attach a maximum of 5 files.',
            'files.*.mimes'        => 'Each file must be a PDF, JPG, or PNG.',
            'files.*.max'          => 'Each file must not exceed 5 MB.',
            'academic_year.regex'  => 'Academic year must be in the format YYYY/YYYY (e.g. 2025/2026).',
        ];
    }
}
