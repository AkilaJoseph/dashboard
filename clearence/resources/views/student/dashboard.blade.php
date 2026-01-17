@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="px-4 sm:px-0">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Welcome, {{ auth()->user()->name }}!</h1>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Requests</dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $stats['total'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                            <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $stats['pending'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Approved</dt>
                            <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $stats['approved'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Rejected</dt>
                            <dd class="mt-1 text-3xl font-semibold text-red-600">{{ $stats['rejected'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-medium text-gray-900">Recent Clearance Requests</h2>
            <a href="{{ route('student.clearances.create') }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                + New Request
            </a>
        </div>

        @if($clearances->isEmpty())
            <p class="text-gray-500 text-center py-8">No clearance requests yet. Start by submitting a new request.</p>
        @else
            <div class="space-y-4">
                @foreach($clearances as $clearance)
                    <div class="border-l-4 @if($clearance->status === 'approved') border-green-500 @elseif($clearance->status === 'rejected') border-red-500 @else border-yellow-500 @endif pl-4 py-2">
                        <a href="{{ route('student.clearances.show', $clearance) }}" class="block hover:bg-gray-50 rounded p-2">
                            <div class="flex justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $clearance->academic_year }} - {{ $clearance->semester }}</p>
                                    <p class="text-sm text-gray-500">{{ $clearance->submitted_at->format('M d, Y') }}</p>
                                </div>
                                <span class="text-sm font-semibold {{ $clearance->status === 'approved' ? 'text-green-600' : ($clearance->status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}">
                                    {{ ucfirst(str_replace('_', ' ', $clearance->status)) }}
                                </span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('student.clearances.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                    View all clearances →
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
