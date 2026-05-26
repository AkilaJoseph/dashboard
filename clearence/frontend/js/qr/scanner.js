/**
 * scanner.js — Officer QR scanner using html5-qrcode.
 *
 * Wraps html5-qrcode, posts the decoded JWT to the department scan API,
 * populates the result panel, and wires up approve/reject actions that
 * point at the existing officer approval routes.
 *
 * Usage (from a Blade <script type="module">):
 *   import DeptScanner from '/build/qr/scanner.js';
 *   window.deptScanner = new DeptScanner({ ...options });
 */

import { Html5Qrcode } from 'html5-qrcode';

export default class DeptScanner {
    #opts;
    #scanner    = null;
    #running    = false;
    #approvalId = null;

    constructor(opts) {
        this.#opts = opts;
        this.#wireManualForm();
    }

    // ── Public API ─────────────────────────────────────────────────────────────

    async toggle() {
        if (this.#running) {
            await this.#stop();
        } else {
            await this.#start();
        }
    }

    async decide(action) {
        if (! this.#approvalId) return;

        const comments = document.getElementById(this.#opts.commentsId)?.value ?? '';

        if (action === 'reject' && ! comments.trim()) {
            this.#setActionMsg('Comments are required to reject.', false);
            return;
        }

        const urlTpl = action === 'approve' ? this.#opts.approveUrlTpl : this.#opts.rejectUrlTpl;
        const url    = urlTpl.replace('{id}', this.#approvalId);

        this.#setActionMsg('Submitting…', null);
        this.#setDecisionButtons(false);

        try {
            const res = await fetch(url, {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN':  this.#opts.csrfToken,
                    'Accept':        'application/json',
                },
                credentials: 'same-origin',
                body: new URLSearchParams({ comments }),
            });

            if (res.ok || res.redirected) {
                this.#setActionMsg(
                    action === 'approve' ? 'Approved successfully.' : 'Rejected.',
                    true,
                );
                // Hide action panel — decision done
                document.getElementById(this.#opts.actionPanelId).style.display = 'none';
                // Refresh status badge
                this.#el('approvalStatus').textContent = action === 'approve' ? '✓ Approved' : '✗ Rejected';
                this.#el('approvalStatus').style.color  = action === 'approve' ? '#059669' : '#ef4444';
            } else {
                const json = await res.json().catch(() => ({}));
                this.#setActionMsg(json.message ?? `Error ${res.status}`, false);
                this.#setDecisionButtons(true);
            }
        } catch (err) {
            this.#setActionMsg('Network error: ' + err.message, false);
            this.#setDecisionButtons(true);
        }
    }

    reset() {
        this.#approvalId = null;
        document.getElementById(this.#opts.resultPanelId).style.display = 'none';
        document.getElementById(this.#opts.actionPanelId).style.display = 'none';
        this.#setStatus('');
        const input = document.getElementById(this.#opts.manualInputId);
        if (input) input.value = '';
    }

    // ── Private — scanner lifecycle ────────────────────────────────────────────

    async #start() {
        this.#setStatus('Starting camera…');

        const placeholder = document.getElementById(this.#opts.placeholderId);
        if (placeholder) placeholder.style.display = 'none';

        this.#scanner = new Html5Qrcode(this.#opts.containerId);

        try {
            await this.#scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                (decoded) => this.#onDecode(decoded),
                () => {},  // per-frame error — suppress
            );
            this.#running = true;
            this.#setBtnLabel('Stop Scanner');
            this.#setStatus('Point camera at the student\'s QR code.');
        } catch (err) {
            this.#setStatus('Camera error: ' + err.message);
            if (placeholder) placeholder.style.display = '';
        }
    }

    async #stop() {
        if (this.#scanner && this.#running) {
            await this.#scanner.stop().catch(() => {});
            this.#scanner = null;
        }
        this.#running = false;
        this.#setBtnLabel('Start Scanner');
        this.#setStatus('');
        const placeholder = document.getElementById(this.#opts.placeholderId);
        if (placeholder) placeholder.style.display = '';
    }

    async #onDecode(token) {
        // Pause scanning while we process
        await this.#stop();
        await this.#verifyToken(token);
    }

    // ── Private — API call ─────────────────────────────────────────────────────

    async #verifyToken(token) {
        this.#setStatus('Verifying…');

        try {
            const res = await fetch(this.#opts.scanApiUrl, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.#opts.csrfToken,
                    'Accept':       'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ token }),
            });

            const json = await res.json();

            if (! res.ok) {
                this.#setStatus('Error: ' + (json.error ?? `HTTP ${res.status}`));
                this.#setBtnLabel('Start Scanner');
                return;
            }

            this.#renderResult(json);
            this.#setStatus('');
        } catch (err) {
            this.#setStatus('Network error: ' + err.message);
        }
    }

    // ── Private — DOM helpers ──────────────────────────────────────────────────

    #renderResult(data) {
        const s  = data.student;
        const cl = data.clearance;
        const ap = data.approval;

        this.#approvalId = ap.id;

        // Student
        this.#el('studentInitial').textContent   = (s.name ?? '?').charAt(0).toUpperCase();
        this.#el('studentName').textContent       = s.name      ?? '—';
        this.#el('studentId').textContent         = s.student_id ?? '—';
        this.#el('studentProgramme').textContent  = s.programme  ?? '—';
        this.#el('studentCollege').textContent    = s.college    ?? '—';

        // Clearance
        this.#el('clearanceType').textContent     = this.#capitalize(cl.clearance_type ?? '—');
        this.#el('clearanceStatus').textContent   = this.#capitalize(cl.status ?? '—');
        this.#el('clearanceYear').textContent     = cl.academic_year ?? '—';
        this.#el('clearanceSemester').textContent = cl.semester       ?? '—';

        // Approval
        this.#el('approvalDept').textContent = data.department ?? '—';

        const statusColors = { approved: '#059669', rejected: '#ef4444', pending: '#d97706' };
        const statusLabels = { approved: '✓ Approved', rejected: '✗ Rejected', pending: '● Pending' };
        const color = statusColors[ap.status] ?? '#64748b';
        this.#el('approvalStatus').textContent = statusLabels[ap.status] ?? ap.status;
        this.#el('approvalStatus').style.color = color;
        this.#el('approvalStatus').style.fontWeight = '700';

        document.getElementById(this.#opts.resultPanelId).style.display = 'block';

        // Only show action panel if still pending
        const actionPanel = document.getElementById(this.#opts.actionPanelId);
        actionPanel.style.display = ap.status === 'pending' ? 'block' : 'none';

        if (ap.status !== 'pending') {
            this.#setActionMsg('', null);
        }

        this.#setDecisionButtons(true);
        this.#setActionMsg('', null);
    }

    #wireManualForm() {
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById(this.#opts.manualFormId);
            form?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const token = document.getElementById(this.#opts.manualInputId)?.value?.trim();
                if (token) await this.#verifyToken(token);
            });
        });
    }

    #el(fieldKey) {
        return document.getElementById(this.#opts.fields[fieldKey]);
    }

    #setStatus(text) {
        const el = document.getElementById(this.#opts.statusId);
        if (el) el.textContent = text;
    }

    #setActionMsg(text, ok) {
        const el = document.getElementById(this.#opts.actionMsgId);
        if (! el) return;
        el.textContent = text;
        el.style.color = ok === true ? '#059669' : ok === false ? '#ef4444' : '#94a3b8';
    }

    #setDecisionButtons(enabled) {
        ['btn-approve', 'btn-reject'].forEach(id => {
            const btn = document.getElementById(id);
            if (btn) btn.disabled = ! enabled;
        });
    }

    #setBtnLabel(text) {
        const btn = document.getElementById('btn-scan');
        if (btn) btn.textContent = text;
    }

    #capitalize(str) {
        return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
    }
}
