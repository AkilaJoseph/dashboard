<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Clearance System')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    @auth
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <h1 class="text-xl font-bold text-gray-800">Clearance System</h1>
                    </div>
                    <div class="ml-6 flex space-x-8">
                        @if(auth()->user()->isStudent())
                            <a href="{{ route('student.dashboard') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">Dashboard</a>
                            <a href="{{ route('student.clearances.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">My Clearances</a>
                        @elseif(auth()->user()->isOfficer())
                            <a href="{{ route('officer.dashboard') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">Dashboard</a>
                            <a href="{{ route('officer.approvals.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">Approvals</a>
                        @elseif(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">Dashboard</a>
                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">Users</a>
                            <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">Departments</a>
                            <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900">Reports</a>
                        @endif
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4">{{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-700 hover:text-gray-900">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
