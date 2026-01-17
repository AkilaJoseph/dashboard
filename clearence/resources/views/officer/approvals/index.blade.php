@extends('layouts.app')

@section('title', 'Clearance Approvals')

@section('content')
<div class="px-4 sm:px-0">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Clearance Approval Requests</h1>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        @if($approvals->isEmpty())
            <div class="text-center py-12">
                <p class="text-gray-500">No pending approval requests.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-200">
                @foreach($approvals as $approval)
                    <li>
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-indigo-600">
                                        {{ $approval->clearance->user->name }} ({{ $approval->clearance->user->student_id }})
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $approval->clearance->academic_year }} - {{ $approval->clearance->semester }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Submitted: {{ $approval->clearance->submitted_at->format('M d, Y') }}
                                    </p>
                                </div>
                                <div class="ml-2 flex items-center space-x-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($approval->status === 'approved') bg-green-100 text-green-800
                                        @elseif($approval->status === 'rejected') bg-red-100 text-red-800
                                        @else bg-yellow-100 text-yellow-800
                                        @endif">
                                        {{ ucfirst($approval->status) }}
                                    </span>
                                    <a href="{{ route('officer.approvals.show', $approval) }}"
                                       class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        Review
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="mt-4">
        {{ $approvals->links() }}
    </div>
</div>
@endsection
