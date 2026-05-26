<?php

namespace App\Http\Controllers\Officer;

use App\Http\Controllers\Controller;

class DepartmentScanController extends Controller
{
    public function show()
    {
        return view('officer.scan');
    }
}
