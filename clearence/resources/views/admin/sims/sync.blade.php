@extends('layouts.app')
@section('title','Sync Students')
@section('page-title','Sync Student from SIMS')
@section('page-subtitle','Fetch and import student data from the Student Information Management System')

@section('content')
<div style="max-width:720px;margin:0 auto;display:flex;flex-direction:column;gap:18px;">

    @if(!$isConfigured)
    <div class="flash-error" style="padding:14px 18px;font-size:13px;">
        SIMS is not configured.
        <a href="{{ route('admin.sims.settings') }}" style="font-weight:700;color:#991b1b;">Configure now →</a>
    </div>
    @endif

    {{-- Fetch preview --}}
    <div class="glow-card">
        <h3 style="font-size:14px;font-weight:700;color:var(--text);margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid var(--border);">
            Step 1 — Fetch Student Data
        </h3>
        <div style="display:flex;gap:10px;margin-bottom:16px;">
            <input type="text" id="reg-input" class="glow-input" placeholder="Enter Registration No. e.g. 22100534050098" style="flex:1;">
            <button onclick="fetchStudent()" class="btn-glow" id="fetch-btn" {{ !$isConfigured ? 'disabled' : '' }}>
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                Fetch
            </button>
        </div>

        <div id="preview" style="display:none;">
            <div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:8px;padding:14px 16px;margin-bottom:16px;">
                <p style="font-size:11px;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:0.07em;margin:0 0 10px;">Student Found in SIMS</p>
                <table style="width:100%;border-collapse:collapse;" id="preview-table"></table>
            </div>

            {{-- Raw scraped data toggle --}}
            <details style="margin-bottom:16px;border:1px solid var(--border);border-radius:8px;overflow:hidden;">
                <summary style="padding:10px 14px;font-size:12px;font-weight:600;color:var(--text-muted);cursor:pointer;background:#f8fafc;list-style:none;">
                    ▸ Show raw scraped labels (for debugging field mapping)
                </summary>
                <div id="raw-table-wrap" style="padding:12px 14px;overflow-x:auto;"></div>
            </details>

            <h3 style="font-size:13px;font-weight:700;color:var(--text);margin:0 0 12px;">Step 2 — Set Login Credentials</h3>
            <form method="POST" action="{{ route('admin.sims.import') }}" id="import-form">
                @csrf
                <input type="hidden" name="reg_no" id="form-reg-no">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Email Address *</label>
                        <input type="email" name="email" id="form-email" class="glow-input" required placeholder="student@must.ac.tz">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:var(--text-muted);display:block;margin-bottom:5px;">Password <span style="font-weight:400;">(default: reg no)</span></label>
                        <input type="text" name="password" class="glow-input" placeholder="Leave blank to use reg no">
                    </div>
                </div>
                <button type="submit" class="btn-glow">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Import / Update Student
                </button>
            </form>
        </div>

        <div id="fetch-error" style="display:none;" class="flash-error" style="padding:10px 14px;font-size:12px;"></div>
    </div>

    {{-- Synced students list --}}
    <div class="glow-card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
            <p style="font-size:13px;font-weight:700;color:var(--text);margin:0;">Synced Students</p>
        </div>
        @php $students = \App\Models\User::where('role','student')->whereNotNull('sims_synced_at')->latest('sims_synced_at')->take(20)->get(); @endphp
        @if($students->isEmpty())
        <p style="padding:20px;text-align:center;color:var(--text-muted);font-size:12px;">No students synced yet.</p>
        @else
        <table class="glow-table" style="width:100%;border-collapse:collapse;">
            <thead><tr>
                <th style="padding:11px 16px;text-align:left;">Student</th>
                <th style="padding:11px 16px;text-align:left;">Reg No</th>
                <th style="padding:11px 16px;text-align:left;">Programme</th>
                <th style="padding:11px 16px;text-align:left;">Last Sync</th>
                <th style="padding:11px 16px;text-align:left;"></th>
            </tr></thead>
            <tbody>
            @foreach($students as $s)
            <tr>
                <td style="padding:10px 16px;">
                    <p style="font-size:13px;font-weight:600;margin:0;">{{ $s->name }}</p>
                    <p style="font-size:11px;color:var(--text-muted);margin:0;">{{ $s->email }}</p>
                </td>
                <td style="padding:10px 16px;font-size:12px;font-family:monospace;color:#d97706;">{{ $s->registration_number ?? '—' }}</td>
                <td style="padding:10px 16px;font-size:12px;color:var(--text-muted);">{{ Str::limit($s->entry_programme ?? $s->programme ?? '—', 35) }}</td>
                <td style="padding:10px 16px;font-size:11px;color:var(--text-muted);">{{ $s->sims_synced_at?->diffForHumans() ?? '—' }}</td>
                <td style="padding:10px 16px;">
                    <form method="POST" action="{{ route('admin.sims.resync', $s) }}">
                        @csrf
                        <button type="submit" style="font-size:11px;font-weight:600;color:var(--green);background:none;border:none;cursor:pointer;">Re-sync</button>
                    </form>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

@push('scripts')
<script>
function fetchStudent() {
    const regNo = document.getElementById('reg-input').value.trim();
    if (!regNo) return;
    const btn = document.getElementById('fetch-btn');
    btn.disabled = true; btn.textContent = 'Fetching...';

    fetch('{{ route("admin.sims.fetch") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ reg_no: regNo }),
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false; btn.textContent = 'Fetch';
        document.getElementById('fetch-error').style.display = 'none';

        if (!res.success) {
            const errEl = document.getElementById('fetch-error');
            errEl.textContent = res.message || 'Fetch failed.';
            errEl.style.display = 'block';
            document.getElementById('preview').style.display = 'none';
            return;
        }

        const d = res.data;
        document.getElementById('form-reg-no').value = regNo;
        document.getElementById('form-email').value  = '';

        const labels = {
            name: 'Full Name', registration_number: 'Reg No', admission_number: 'Admission No',
            entry_year: 'Entry Year', entry_programme: 'Programme', entry_category: 'Entry Category',
            campus: 'Campus', gender: 'Gender', birth_date: 'Birth Date',
            nationality: 'Nationality', disability: 'Disability',
        };
        const table = document.getElementById('preview-table');
        table.innerHTML = Object.entries(labels).filter(([k]) => d[k]).map(([k, label]) =>
            `<tr>
                <td style="font-size:11px;font-weight:700;color:#374151;padding:3px 0;width:40%;">${label}</td>
                <td style="font-size:12px;color:#1e293b;padding:3px 0;">${d[k] || '—'}</td>
            </tr>`
        ).join('');

        document.getElementById('preview').style.display = 'block';

        // Show raw scraped labels for debugging
        if (res.raw && Object.keys(res.raw).length) {
            const rawWrap = document.getElementById('raw-table-wrap');
            rawWrap.innerHTML = '<p style="font-size:11px;color:var(--text-muted);margin:0 0 8px;">All labels scraped from SIMS profile page:</p>'
                + '<table style="width:100%;border-collapse:collapse;font-size:11px;">'
                + Object.entries(res.raw).map(([k, v]) =>
                    `<tr><td style="padding:3px 6px;border-bottom:1px solid #f1f5f9;color:#374151;font-weight:600;width:50%;">${k}</td><td style="padding:3px 6px;border-bottom:1px solid #f1f5f9;color:#1e293b;">${v}</td></tr>`
                ).join('')
                + '</table>';
        }
    })
    .catch(err => { btn.disabled = false; btn.textContent = 'Fetch'; console.error(err); });
}

document.getElementById('reg-input').addEventListener('keypress', e => { if (e.key === 'Enter') fetchStudent(); });
</script>
@endpush
@endsection
