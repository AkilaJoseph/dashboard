<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $clearances = $user->clearances()->latest()->take(5)->get();
        $stats = [
            'total' => $user->clearances()->count(),
            'pending' => $user->clearances()->where('status', 'pending')->count(),
            'approved' => $user->clearances()->where('status', 'approved')->count(),
            'rejected' => $user->clearances()->where('status', 'rejected')->count(),
        ];

        return view('student.dashboard', compact('clearances', 'stats'));
    }
}
