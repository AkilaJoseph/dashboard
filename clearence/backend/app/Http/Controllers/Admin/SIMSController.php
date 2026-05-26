<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\SIMSScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SIMSController extends Controller
{
    private function scraper(): SIMSScraperService
    {
        return new SIMSScraperService();
    }

    public function settings()
    {
        return view('admin.sims.settings', [
            'simsUrl'       => SystemSetting::get('sims_url', 'https://sims.must.ac.tz'),
            'loginUrl'      => SystemSetting::get('sims_login_url', ''),
            'profilePath'   => SystemSetting::get('sims_profile_path', '/studentprofile/'),
            'simsUsername'  => SystemSetting::get('sims_username', ''),
            'simsPassword'  => SystemSetting::get('sims_password', ''),
            'usernameField' => SystemSetting::get('sims_username_field', 'username'),
            'passwordField' => SystemSetting::get('sims_password_field', 'password'),
            'isConfigured'  => $this->scraper()->isConfigured(),
        ]);
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'sims_url'            => 'required|url',
            'sims_login_url'      => 'nullable|url',
            'sims_profile_path'   => 'required|string',
            'sims_username'       => 'required|string',
            'sims_password'       => 'required|string',
            'sims_username_field' => 'required|string',
            'sims_password_field' => 'required|string',
        ]);

        foreach ($request->only([
            'sims_url', 'sims_login_url', 'sims_profile_path',
            'sims_username', 'sims_password',
            'sims_username_field', 'sims_password_field',
        ]) as $key => $value) {
            SystemSetting::set($key, $value);
        }

        return back()->with('success', 'SIMS settings saved.');
    }

    public function sync()
    {
        return view('admin.sims.sync', [
            'isConfigured' => $this->scraper()->isConfigured(),
        ]);
    }

    public function fetchStudent(Request $request)
    {
        $request->validate(['reg_no' => 'required|string']);

        try {
            $data = $this->scraper()->fetchStudent($request->reg_no);
            $debug = $data['_raw'] ?? [];
            unset($data['_raw']);
            return response()->json(['success' => true, 'data' => $data, 'raw' => $debug]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function importStudent(Request $request)
    {
        $request->validate([
            'reg_no'   => 'required|string',
            'email'    => 'required|email',
            'password' => 'nullable|string|min:6',
        ]);

        try {
            $data = $this->scraper()->fetchStudent($request->reg_no);
            unset($data['_raw']);
        } catch (\Throwable $e) {
            return back()->with('error', 'SIMS scrape failed: ' . $e->getMessage());
        }

        $user = User::updateOrCreate(
            ['registration_number' => $data['registration_number'] ?? $request->reg_no],
            array_merge($data, [
                'email'     => $request->email,
                'role'      => 'student',
                'password'  => Hash::make($request->password ?: $request->reg_no),
                'is_active' => true,
            ])
        );

        return back()->with('success', "Student {$user->name} imported/updated successfully.");
    }

    public function resync(User $user)
    {
        if (!$user->registration_number) {
            return back()->with('error', 'Student has no registration number.');
        }

        try {
            $data = $this->scraper()->fetchStudent($user->registration_number);
            unset($data['_raw']);
            $user->update($data);
        } catch (\Throwable $e) {
            return back()->with('error', 'Re-sync failed: ' . $e->getMessage());
        }

        return back()->with('success', "{$user->name} re-synced from SIMS.");
    }
}
