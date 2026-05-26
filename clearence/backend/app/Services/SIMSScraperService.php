<?php

namespace App\Services;

use App\Models\SystemSetting;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use DOMDocument;
use DOMXPath;

class SIMSScraperService
{
    private Client    $http;
    private CookieJar $jar;

    private string $baseUrl;
    private string $loginUrl;
    private string $profilePath;
    private string $username;
    private string $password;
    private string $usernameField;
    private string $passwordField;

    public function __construct()
    {
        $this->baseUrl       = rtrim(SystemSetting::get('sims_url', 'https://sims.must.ac.tz'), '/');
        $savedLoginUrl       = SystemSetting::get('sims_login_url', '');
        $this->loginUrl      = $savedLoginUrl ?: $this->baseUrl . '/logincheck';
        $this->profilePath   = SystemSetting::get('sims_profile_path', '/studentprofile/');
        $this->username      = SystemSetting::get('sims_username', '');
        $this->password      = SystemSetting::get('sims_password', '');
        $this->usernameField = SystemSetting::get('sims_username_field', 'username');
        $this->passwordField = SystemSetting::get('sims_password_field', 'password');

        $this->jar  = new CookieJar();
        $this->http = new Client([
            'cookies'         => $this->jar,
            'allow_redirects' => ['max' => 10, 'track_redirects' => true],
            'verify'          => false,
            'timeout'         => 20,
            'headers'         => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ],
        ]);
    }

    public function isConfigured(): bool
    {
        return !empty($this->baseUrl) && !empty($this->username) && !empty($this->password);
    }

    // ── Public ──────────────────────────────────────────────────────────────

    public function fetchStudent(string $regNo): array
    {
        $this->login();

        $url      = $this->baseUrl . $this->profilePath . '?regNo=' . urlencode($regNo);
        $response = $this->http->get($url);
        $html     = (string) $response->getBody();

        if ($this->isLoginPage($html)) {
            throw new \RuntimeException('Session expired or login failed. Check SIMS credentials.');
        }

        $data = $this->parseProfilePage($html);

        if (empty($data)) {
            throw new \RuntimeException("No student data found for reg no: {$regNo}");
        }

        return $this->mapToUserFields($data, $regNo);
    }

    // ── Login ────────────────────────────────────────────────────────────────

    private function login(): void
    {
        // 1. Load login page to collect session cookie + hidden fields
        $loginPage = (string) $this->http->get($this->baseUrl . '/login')->getBody();

        $hiddenFields = $this->extractHiddenFields($loginPage);
        $csrfToken    = $this->extractCsrfToken($loginPage);

        // 2. Submit credentials
        $formData = array_merge($hiddenFields, [
            $this->usernameField => $this->username,
            $this->passwordField => $this->password,
        ]);
        if ($csrfToken) {
            $formData['_token'] = $csrfToken;
        }

        $this->http->post($this->loginUrl, ['form_params' => $formData]);
    }

    private function isLoginPage(string $html): bool
    {
        return stripos($html, 'name="' . $this->usernameField . '"') !== false
            || stripos($html, 'type="password"') !== false
            || stripos($html, 'login') !== false && stripos($html, 'studentprofile') === false;
    }

    // ── Parsing ──────────────────────────────────────────────────────────────

    private function parseProfilePage(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new DOMXPath($dom);

        $data = [];

        // Strategy 1: table rows with 2 cells (label | value)
        $rows = $xpath->query('//table//tr');
        foreach ($rows as $row) {
            $cells = $xpath->query('.//td', $row);
            if ($cells->length >= 2) {
                $label = trim($cells->item(0)->textContent);
                $value = trim($cells->item(1)->textContent);
                if ($label && $value && strlen($label) < 60) {
                    $data[$label] = $value;
                }
            }
        }

        // Strategy 2: definition lists (dl > dt + dd)
        if (empty($data)) {
            $dts = $xpath->query('//dl/dt');
            foreach ($dts as $dt) {
                $dd = $xpath->query('following-sibling::dd[1]', $dt)->item(0);
                if ($dd) {
                    $data[trim($dt->textContent)] = trim($dd->textContent);
                }
            }
        }

        // Strategy 3: label + span/input siblings
        if (empty($data)) {
            $labels = $xpath->query('//label');
            foreach ($labels as $label) {
                $forAttr = $label->getAttribute('for');
                if ($forAttr) {
                    $field = $xpath->query('//*[@id="' . $forAttr . '"]')->item(0);
                    if ($field) {
                        $value = $field->getAttribute('value') ?: trim($field->textContent);
                        $data[trim($label->textContent)] = $value;
                    }
                }
            }
        }

        return $data;
    }

    private function extractCsrfToken(string $html): string
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR);
        $xpath = new DOMXPath($dom);

        foreach (['_token', 'csrf_token', 'csrfmiddlewaretoken', 'authenticity_token'] as $name) {
            $node = $xpath->query('//input[@name="' . $name . '"]')->item(0);
            if ($node) return $node->getAttribute('value');
        }

        $meta = $xpath->query('//meta[@name="csrf-token"]')->item(0);
        if ($meta) return $meta->getAttribute('content');

        return '';
    }

    private function extractHiddenFields(string $html): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html, LIBXML_NOERROR);
        $xpath  = new DOMXPath($dom);
        $fields = [];

        $nodes = $xpath->query('//input[@type="hidden"]');
        foreach ($nodes as $node) {
            $name = $node->getAttribute('name');
            if ($name && $name !== $this->usernameField && $name !== $this->passwordField) {
                $fields[$name] = $node->getAttribute('value');
            }
        }

        return $fields;
    }

    // ── Field Mapping ────────────────────────────────────────────────────────

    private function mapToUserFields(array $raw, string $regNo): array
    {
        // Fuzzy key lookup — matches partial, case-insensitive label names
        $get = function (array $keys) use ($raw): ?string {
            foreach ($keys as $key) {
                foreach ($raw as $label => $value) {
                    if (stripos($label, $key) !== false && !empty(trim($value))) {
                        return trim($value);
                    }
                }
            }
            return null;
        };

        $firstName  = $get(['First Name', 'Firstname', 'Given Name']);
        $middleName = $get(['Middle Name', 'Middlename', 'Other Name']);
        $lastName   = $get(['Last Name', 'Lastname', 'Surname', 'Family Name']);
        $fullName   = trim("$firstName $middleName $lastName") ?: $get(['Full Name', 'Student Name', 'Name']);

        return [
            'first_name'          => $firstName,
            'middle_name'         => $middleName,
            'last_name'           => $lastName,
            'name'                => $fullName ?: $regNo,
            'registration_number' => $get(['Registration No', 'Reg No', 'RegNo', 'Registration Number']) ?? $regNo,
            'student_id'          => $get(['Registration No', 'Reg No', 'Student ID']) ?? $regNo,
            'admission_number'    => $get(['Admission No', 'Admission Number', 'Adm No']),
            'entry_year'          => $get(['Entry Year', 'Year of Entry', 'Academic Year']),
            'entry_programme'     => $get(['Entry Programme', 'Programme', 'Course']),
            'programme'           => $get(['Entry Programme', 'Programme', 'Course']),
            'entry_category'      => $get(['Entry Category', 'Category', 'Mode of Entry']),
            'college'             => $get(['College', 'Faculty', 'School', 'Department']),
            'campus'              => $get(['Campus', 'Campus Name']),
            'year_of_study'       => $get(['Year of Study', 'Current Year', 'Study Year']),
            'gender'              => $get(['Gender', 'Sex']),
            'birth_date'          => $this->parseDate($get(['Birth Date', 'Date of Birth', 'DOB', 'Birthday'])),
            'nationality'         => $get(['Nationality', 'Citizenship', 'Country']),
            'disability'          => $get(['Disability', 'Disability Status']) ?? 'None',
            'sims_synced_at'      => now(),
            '_raw'                => $raw,  // passed back for debug, stripped before saving
        ];
    }

    private function parseDate(?string $date): ?string
    {
        if (!$date) return null;
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
