@extends('layouts.app')

@section('title', 'Review Clearance Request')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Review Clearance Request</h1>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Student Information</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Student Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $approval->clearance->user->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Student ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $approval->clearance->user->student_id }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $approval->clearance->user->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $approval->clearance->user->phone ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Clearance Details</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Academic Year</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $approval->clearance->academic_year }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Semester</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $approval->clearance->semester }}</dd>
                </div>
                @if($approval->clearance->reason)
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Reason</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $approval->clearance->reason }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>

    @if($approval->status === 'pending')
    <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Review Action</h3>

        <form method="POST" action="{{ route('officer.approvals.approve', $approval) }}" class="mb-3">
            @csrf
            <div class="mb-4">
                <label for="approve_comments" class="block text-sm font-medium text-gray-700">Comments (Optional)</label>
                <textarea name="comments" id="approve_comments" rows="3"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
            </div>
            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Approve Clearance
            </button>
        </form>

        <form method="POST" action="{{ route('officer.approvals.reject', $approval) }}">
            @csrf
            <div class="mb-4">
                <label for="reject_comments" class="block text-sm font-medium text-gray-700">Rejection Reason *</label>
                <textarea name="comments" id="reject_comments" rows="3" required
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500"></textarea>
            </div>
            <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Reject Clearance
            </button>
        </form>
    </div>
    @else
    <div class="bg-white shadow sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Decision</h3>
        <p class="text-sm text-gray-900">
            Status: <span class="font-semibold @if($approval->status === 'approved') text-green-600 @else text-red-600 @endif">
                {{ ucfirst($approval->status) }}
            </span>
        </p>
        @if($approval->comments)
        <p class="text-sm text-gray-700 mt-2"><strong>Comments:</strong> {{ $approval->comments }}</p>
        @endif
        <p class="text-xs text-gray-500 mt-2">Reviewed on: {{ $approval->reviewed_at->format('F d, Y h:i A') }}</p>
    </div>
    @endif

    <div class="mt-6">
        <a href="{{ route('officer.approvals.index') }}"
           class="text-indigo-600 hover:text-indigo-900">
            &larr; Back to Approvals
        </a>
    </div>
</div>
@endsection
