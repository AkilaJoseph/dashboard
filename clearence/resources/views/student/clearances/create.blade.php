@extends('layouts.app')

@section('title', 'Submit Clearance Request')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Submit Clearance Request</h1>

    <div class="bg-white shadow sm:rounded-lg p-6">
        <form method="POST" action="{{ route('student.clearances.store') }}">
            @csrf

            <div class="mb-4">
                <label for="academic_year" class="block text-sm font-medium text-gray-700">Academic Year</label>
                <input type="text" name="academic_year" id="academic_year" required
                       value="{{ old('academic_year', date('Y') . '/' . (date('Y') + 1)) }}"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                @error('academic_year')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="semester" class="block text-sm font-medium text-gray-700">Semester</label>
                <select name="semester" id="semester" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="First Semester">First Semester</option>
                    <option value="Second Semester">Second Semester</option>
                </select>
                @error('semester')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700">Reason (Optional)</label>
                <textarea name="reason" id="reason" rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('student.clearances.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
