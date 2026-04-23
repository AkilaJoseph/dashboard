<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Department::class);
        $departments = Department::withCount(['approvals', 'officers'])->orderBy('priority')->get();
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $this->authorize('create', Department::class);
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Department::class);
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:10|unique:departments,code',
            'description' => 'nullable|string',
            'priority'    => 'required|integer|min:1',
        ]);

        Department::create([
            'name'        => $request->name,
            'code'        => strtoupper($request->code),
            'description' => $request->description,
            'priority'    => $request->priority,
            'is_active'   => true,
        ]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        $this->authorize('update', $department);
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $this->authorize('update', $department);
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:10|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'priority'    => 'required|integer|min:1',
            'is_active'   => 'boolean',
        ]);

        $department->update([
            'name'        => $request->name,
            'code'        => strtoupper($request->code),
            'description' => $request->description,
            'priority'    => $request->priority,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $this->authorize('delete', $department);
        $department->delete();
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted.');
    }
}
