@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="px-4 sm:px-0">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">System Overview</h1>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_users'] }}</dd>
                </dl>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Students</dt>
                    <dd class="mt-1 text-3xl font-semibold text-indigo-600">{{ $stats['total_students'] }}</dd>
                </dl>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Officers</dt>
                    <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ $stats['total_officers'] }}</dd>
                </dl>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Departments</dt>
                    <dd class="mt-1 text-3xl font-semibold text-purple-600">{{ $stats['total_departments'] }}</dd>
                </dl>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Clearances</dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total_clearances'] }}</dd>
                </dl>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                    <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $stats['pending_clearances'] }}</dd>
                </dl>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Approved</dt>
                    <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $stats['approved_clearances'] }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Clearance Requests</h2>

        @if($recent_clearances->isEmpty())
            <p class="text-gray-500 text-center py-8">No clearance requests yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Year</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recent_clearances as $clearance)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $clearance->user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $clearance->academic_year }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($clearance->status === 'approved') bg-green-100 text-green-800
                                        @elseif($clearance->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $clearance->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $clearance->submitted_at->format('M d, Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
