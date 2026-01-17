@extends('layouts.app')

@section('title', 'My Clearances')

@section('content')
<div class="px-4 sm:px-0">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">My Clearance Requests</h1>
        <a href="{{ route('student.clearances.create') }}"
           class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            Submit New Request
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        @if($clearances->isEmpty())
            <div class="text-center py-12">
                <p class="text-gray-500">No clearance requests yet.</p>
                <a href="{{ route('student.clearances.create') }}" class="text-indigo-600 hover:text-indigo-900 mt-2 inline-block">
                    Submit your first clearance request
                </a>
            </div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach($clearances as $clearance)
                    <li>
                        <a href="{{ route('student.clearances.show', $clearance) }}" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-indigo-600 truncate">
                                            {{ $clearance->academic_year }} - {{ $clearance->semester }}
                                        </p>
                                        <p class="mt-2 text-sm text-gray-500">
                                            Submitted: {{ $clearance->submitted_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($clearance->status === 'approved') bg-green-100 text-green-800
                                            @elseif($clearance->status === 'rejected') bg-red-100 text-red-800
                                            @elseif($clearance->status === 'in_progress') bg-yellow-100 text-yellow-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $clearance->status)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <div class="text-sm text-gray-700">
                                        @php
                                            $approved = $clearance->approvals->where('status', 'approved')->count();
                                            $total = $clearance->approvals->count();
                                        @endphp
                                        Progress: {{ $approved }} / {{ $total }} departments approved
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
