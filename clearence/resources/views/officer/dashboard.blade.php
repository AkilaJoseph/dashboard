@extends('layouts.app')

@section('title', 'Officer Dashboard')

@section('content')
<div class="px-4 sm:px-0">
    <h1 class="text-2xl font-semibold text-gray-900 mb-2">{{ auth()->user()->department->name }} Department</h1>
    <p class="text-gray-600 mb-6">Welcome, {{ auth()->user()->name }}</p>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Pending Approvals</dt>
                    <dd class="mt-1 text-3xl font-semibold text-yellow-600">{{ $stats['pending'] }}</dd>
                </dl>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Approved</dt>
                    <dd class="mt-1 text-3xl font-semibold text-green-600">{{ $stats['approved'] }}</dd>
                </dl>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Rejected</dt>
                    <dd class="mt-1 text-3xl font-semibold text-red-600">{{ $stats['rejected'] }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Approval Requests</h2>

        @if($approvals->isEmpty())
            <p class="text-gray-500 text-center py-8">No approval requests.</p>
        @else
            <div class="space-y-3">
                @foreach($approvals as $approval)
                    <div class="border-l-4 @if($approval->status === 'approved') border-green-500 @elseif($approval->status === 'rejected') border-red-500 @else border-yellow-500 @endif pl-4 py-2">
                        <a href="{{ route('officer.approvals.show', $approval) }}" class="block hover:bg-gray-50 rounded p-2">
                            <div class="flex justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $approval->clearance->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $approval->clearance->user->student_id }} - {{ $approval->clearance->academic_year }}</p>
                                </div>
                                <span class="text-sm font-semibold {{ $approval->status === 'approved' ? 'text-green-600' : ($approval->status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}">
                                    {{ ucfirst($approval->status) }}
                                </span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('officer.approvals.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">
                    View all approvals →
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
