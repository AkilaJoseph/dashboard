<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;

class SIMSService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(SystemSetting::get('sims_api_url', ''), '/');
        $this->apiKey  = SystemSetting::get('sims_api_key', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->baseUrl);
    }

    /**
     * Fetch a student from SIMS by registration number.
     * Returns normalized array or throws on failure.
     */
    public function fetchStudent(string $regNo): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
        ])->timeout(15)->get($this->baseUrl . '/api/student', [
            'regNo' => $regNo,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('SIMS API error: ' . $response->status());
        }

        $data = $response->json();

        return $this->normalize($data);
    }

    /**
     * Map SIMS JSON fields → our user fields.
     * Adjust the keys below to match the actual SIMS API response.
     */
    private function normalize(array $data): array
    {
        return [
            'first_name'          => $data['firstName']          ?? $data['first_name']          ?? null,
            'middle_name'         => $data['middleName']         ?? $data['middle_name']         ?? null,
            'last_name'           => $data['lastName']           ?? $data['last_name']           ?? null,
            'name'                => trim(
                ($data['firstName'] ?? '') . ' ' .
                ($data['middleName'] ?? '') . ' ' .
                ($data['lastName']  ?? '')
            ),
            'registration_number' => $data['regNo']              ?? $data['registration_number'] ?? null,
            'student_id'          => $data['regNo']              ?? $data['studentId']           ?? null,
            'admission_number'    => $data['admissionNo']        ?? $data['admission_number']    ?? null,
            'entry_year'          => $data['entryYear']          ?? $data['entry_year']          ?? null,
            'entry_programme'     => $data['entryProgramme']     ?? $data['programme']           ?? null,
            'programme'           => $data['entryProgramme']     ?? $data['programme']           ?? null,
            'entry_category'      => $data['entryCategory']      ?? null,
            'college'             => $data['collegeName']        ?? $data['college']             ?? null,
            'campus'              => $data['campusName']         ?? $data['campus']              ?? null,
            'gender'              => $data['gender']             ?? null,
            'birth_date'          => $data['birthDate']          ?? $data['birth_date']          ?? null,
            'nationality'         => $data['nationality']        ?? null,
            'disability'          => $data['disability']         ?? 'None',
            'year_of_study'       => $data['yearOfStudy']        ?? $data['year_of_study']       ?? null,
            'sims_synced_at'      => now(),
        ];
    }
}
