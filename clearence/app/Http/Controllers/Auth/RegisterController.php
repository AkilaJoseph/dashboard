<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\StrongPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'email'               => 'required|email|unique:users,email',
            'phone'               => 'nullable|string|max:20',
            'student_id'          => 'required|string|max:50|unique:users,student_id',
            'registration_number' => 'nullable|string|max:50',
            'programme'           => 'required|string|max:255',
            'college'             => 'required|string|max:255',
            'year_of_study'       => 'required|string|max:50',
            'password'            => ['required', 'string', 'confirmed', new StrongPassword],
        ]);

        $user = User::create([
            'name'                => $request->name,
            'email'               => $request->email,
            'phone'               => $request->phone,
            'role'                => 'student',
            'student_id'          => strtoupper($request->student_id),
            'registration_number' => $request->registration_number,
            'programme'           => $request->programme,
            'college'             => $request->college,
            'year_of_study'       => $request->year_of_study,
            'password'            => Hash::make($request->password),
            'is_active'           => true,
        ]);

        Auth::login($user);

        return redirect()->route('student.dashboard')
            ->with('success', 'Account created successfully! Welcome to MUST Clearance System.');
    }
}
