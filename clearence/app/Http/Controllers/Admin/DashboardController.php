<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Clearance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_officers' => User::where('role', 'officer')->count(),
            'total_departments' => Department::count(),
            'total_clearances' => Clearance::count(),
            'pending_clearances' => Clearance::where('status', 'pending')->count(),
            'approved_clearances' => Clearance::where('status', 'approved')->count(),
        ];

        $recent_clearances = Clearance::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recent_clearances'));
    }
}
