<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clearance Certificate &mdash; MUST</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page { box-shadow: none !important; margin: 0 !important; border-radius: 0 !important; }
        }
        .border-pattern {
            border: 8px double #7f1d1d;
            box-shadow: inset 0 0 0 3px #fbbf24;
        }
    </style>
</head>
<body class="bg-gray-200 min-h-screen flex flex-col items-center py-10">

<!-- Action Buttons -->
<div class="no-print mb-6 flex space-x-3 flex-wrap gap-2 justify-center">
    <a href="{{ route('student.clearances.certificate', $clearance) }}"
       class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-lg text-white font-semibold text-sm shadow"
       style="background: #059669;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        <span>Download PDF</span>
    </a>
    <button onclick="window.print()"
            class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-lg text-white font-semibold text-sm shadow"
            style="background: #7f1d1d;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        <span>Print</span>
    </button>
    <a href="{{ route('student.clearances.show', $clearance) }}"
       class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-lg text-gray-700 font-semibold text-sm shadow bg-white">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Back</span>
    </a>
</div>

<!-- Certificate Page -->
<div class="page bg-white w-full max-w-3xl shadow-2xl rounded-lg overflow-hidden border-pattern mx-4">

    <!-- Header -->
    <div class="text-center py-6 px-8" style="background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);">
        <div class="flex items-center justify-center space-x-4 mb-3">
            <div class="w-16 h-16 rounded-full bg-white flex items-center justify-center shadow">
                <span class="text-red-900 font-black text-xl">MUST</span>
            </div>
            <div class="text-left">
                <h1 class="text-white font-bold text-xl leading-tight">MBEYA UNIVERSITY OF SCIENCE AND TECHNOLOGY</h1>
                <p class="text-red-200 text-sm">Chuo Kikuu cha Sayansi na Teknolojia Mbeya</p>
                <p class="text-red-300 text-xs">P.O Box 131, Mbeya, Tanzania | www.must.ac.tz</p>
            </div>
        </div>
    </div>

    <!-- Gold Divider -->
    <div class="h-1.5 bg-amber-400"></div>

    <!-- Certificate Body -->
    <div class="px-12 py-8">

        <!-- Certificate Title -->
        <div class="text-center mb-8">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-1">Official Document</p>
            <h2 class="text-2xl font-bold uppercase tracking-wide text-gray-800" style="color: #7f1d1d;">
                Student Clearance Certificate
            </h2>
            <div class="mt-2 flex justify-center">
                <div class="h-0.5 w-32 bg-amber-400"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Certificate No: MUST/CLR/{{ str_pad($clearance->id, 6, '0', STR_PAD_LEFT) }}/{{ date('Y') }}</p>
        </div>

        <!-- Certification Statement -->
        <p class="text-sm text-gray-700 leading-relaxed text-center mb-8">
            This is to certify that the student whose particulars are detailed below has successfully completed
            all departmental clearance requirements as stipulated by Mbeya University of Science and Technology
            for the academic period specified herein.
        </p>

        <!-- Student Details Box -->
        <div class="bg-gray-50 rounded-lg border border-gray-200 p-6 mb-8">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Student Particulars</h3>
            <div class="grid grid-cols-2 gap-y-4 gap-x-8">
                <div>
                    <p class="text-xs text-gray-500">Full Name</p>
                    <p class="text-sm font-bold text-gray-800">{{ $clearance->user->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Registration Number</p>
                    <p class="text-sm font-bold text-gray-800 font-mono">{{ $clearance->user->registration_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">UE Number / Student ID</p>
                    <p class="text-sm font-bold text-gray-800 font-mono">{{ $clearance->user->student_id ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Programme</p>
                    <p class="text-sm font-bold text-gray-800">{{ $clearance->user->programme ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">College</p>
                    <p class="text-sm font-bold text-gray-800">{{ $clearance->user->college ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Academic Year</p>
                    <p class="text-sm font-bold text-gray-800">{{ $clearance->academic_year }} &mdash; {{ $clearance->semester }} Semester</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Clearance Type</p>
                    <p class="text-sm font-bold text-gray-800 capitalize">{{ $clearance->clearance_type }} Clearance</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Date Cleared</p>
                    <p class="text-sm font-bold text-gray-800">{{ $clearance->completed_at ? $clearance->completed_at->format('d F Y') : now()->format('d F Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Department Approvals Table -->
        <div class="mb-8">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Departmental Sign-offs</h3>
            <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="text-left px-4 py-2 text-xs font-semibold text-gray-600">Department</th>
                        <th class="text-left px-4 py-2 text-xs font-semibold text-gray-600">Cleared By</th>
                        <th class="text-left px-4 py-2 text-xs font-semibold text-gray-600">Date</th>
                        <th class="text-center px-4 py-2 text-xs font-semibold text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($clearance->approvals->sortBy('department.priority') as $approval)
                    <tr>
                        <td class="px-4 py-2.5 text-gray-800 font-medium text-xs">{{ $approval->department->name }}</td>
                        <td class="px-4 py-2.5 text-gray-600 text-xs">{{ $approval->officer->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-gray-600 text-xs">{{ $approval->reviewed_at ? $approval->reviewed_at->format('d/m/Y') : '—' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="text-xs font-bold text-green-700">&#10003; Approved</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Signature Area -->
        <div class="grid grid-cols-2 gap-12 mt-10">
            <div class="text-center">
                <div class="h-12 border-b-2 border-gray-400 mb-2"></div>
                <p class="text-xs font-semibold text-gray-700">Registrar's Signature</p>
                <p class="text-xs text-gray-500">Registry Office, MUST</p>
            </div>
            <div class="text-center">
                <div class="h-12 border-b-2 border-gray-400 mb-2"></div>
                <p class="text-xs font-semibold text-gray-700">Student's Signature</p>
                <p class="text-xs text-gray-500">{{ $clearance->user->name }}</p>
            </div>
        </div>
    </div>

    <!-- Gold Divider -->
    <div class="h-1.5 bg-amber-400"></div>

    <!-- Footer -->
    <div class="text-center py-4 px-8 bg-gray-50">
        <p class="text-xs text-gray-500">
            This certificate was generated electronically by the MUST Automated Clearance Management System on {{ now()->format('d F Y, h:i A') }}.
        </p>
        <p class="text-xs text-gray-400 mt-1">Verify authenticity: Registry Office, MUST &mdash; P.O Box 131, Mbeya, Tanzania</p>
    </div>
</div>

<div class="no-print h-10"></div>
</body>
</html>
