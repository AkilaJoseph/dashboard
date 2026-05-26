<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class PendingSyncController extends Controller
{
    public function index()
    {
        return view('student.pending-sync');
    }
}
