@extends('layouts.app')

@section('title', 'Clearance Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Clearance Details</h1>
        @if($clearance->status === 'approved')
            <a href="{{ route('student.clearances.certificate', $clearance) }}"
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Download Certificate
            </a>
        @endif
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Clearance Information</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Academic Year</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $clearance->academic_year }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Semester</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $clearance->semester }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($clearance->status === 'approved') bg-green-100 text-green-800
                            @elseif($clearance->status === 'rejected') bg-red-100 text-red-800
                            @elseif($clearance->status === 'in_progress') bg-yellow-100 text-yellow-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $clearance->status)) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Submitted Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $clearance->submitted_at->format('F d, Y h:i A') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Department Approvals</h3>
        </div>
        <div class="border-t border-gray-200">
            <ul class="divide-y divide-gray-200">
                @foreach($clearance->approvals as $approval)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $approval->department->name }}</p>
                                @if($approval->officer)
                                    <p class="text-xs text-gray-500 mt-1">Reviewed by: {{ $approval->officer->name }}</p>
                                @endif
                                @if($approval->comments)
                                    <p class="text-sm text-gray-600 mt-2">{{ $approval->comments }}</p>
                                @endif
                                @if($approval->reviewed_at)
                                    <p class="text-xs text-gray-500 mt-1">{{ $approval->reviewed_at->format('M d, Y h:i A') }}</p>
                                @endif
                            </div>
                            <div>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($approval->status === 'approved') bg-green-100 text-green-800
                                    @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($approval->status) }}
                                </span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('student.clearances.index') }}"
           class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Clearances
        </a>
    </div>
</div>
@endsection
