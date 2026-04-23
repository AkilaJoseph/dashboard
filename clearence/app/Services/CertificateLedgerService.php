<?php

namespace App\Services;

use App\Models\CertificateLedger;
use App\Models\Clearance;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Append-only tamper-evident ledger for issued clearance certificates.
 *
 * Each row is linked to the previous by its hash (blockchain-style chain).
 * The entire row is then HMAC-signed so that both database tampering AND
 * key-compromise can be independently detected.
 *
 * Canonical payload (deterministic key order):
 *   reference_no · student · clearance_type · approval_chain · final_approver · issued_at
 *
 * .env entry needed:
 *   LEDGER_HMAC_KEY=<32+ random bytes as hex>
 *   Run: php artisan tinker --execute="echo bin2hex(random_bytes(32));"
 */
class CertificateLedgerService
{
    // ── Public API ─────────────────────────────────────────────────────────────

    /**
     * Append a ledger entry for a just-approved clearance.
     *
     * Must be called inside the same DB transaction as the approval, or wrapped
     * in one — the sequence number is assigned under a lock to prevent races.
     *
     * @param  Clearance  $clearance  Must have user, approvals.department, approvals.officer loaded.
     */
    public function append(Clearance $clearance): CertificateLedger
    {
        return DB::transaction(function () use ($clearance) {
            // Lock last row to get a consistent previous_hash + next sequence
            $last = CertificateLedger::orderByDesc('sequence')->lockForUpdate()->first();

            $previousHash = $last ? $last->certificate_hash : str_repeat('0', 64);
            $sequence     = $last ? ($last->sequence + 1) : 1;
            $signedAt     = now();

            $canonicalJson = $this->buildCanonicalJson($clearance, $signedAt->format('Y-m-d\TH:i:s\Z'));
            $hash          = hash('sha256', $canonicalJson);

            $signature = $this->sign($hash, $previousHash, $sequence, $signedAt->format('Y-m-d\TH:i:s\Z'));

            return CertificateLedger::create([
                'clearance_id'     => $clearance->id,
                'certificate_hash' => $hash,
                'previous_hash'    => $previousHash,
                'sequence'         => $sequence,
                'signed_at'        => $signedAt,
                'signature'        => $signature,
            ]);
        });
    }

    /**
     * Recompute the hash for a ledger entry and verify its HMAC signature.
     *
     * Returns an array:
     *   hash_valid  bool  — recomputed hash matches stored hash
     *   sig_valid   bool  — HMAC signature is valid (key is correct)
     *   status      string — 'verified' | 'tampered' | 'invalid-signature' | 'key-missing'
     */
    public function verify(CertificateLedger $entry): array
    {
        $clearance = $entry->clearance->load('user', 'approvals.department', 'approvals.officer');

        $issuedAt      = $entry->signed_at->format('Y-m-d\TH:i:s\Z');
        $canonicalJson = $this->buildCanonicalJson($clearance, $issuedAt);
        $recomputed    = hash('sha256', $canonicalJson);
        $hashValid     = hash_equals($entry->certificate_hash, $recomputed);

        try {
            $expectedSig = $this->sign(
                $entry->certificate_hash,
                $entry->previous_hash,
                $entry->sequence,
                $issuedAt,
            );
            $sigValid = hash_equals($expectedSig, $entry->signature);
        } catch (RuntimeException) {
            return ['hash_valid' => $hashValid, 'sig_valid' => false, 'status' => 'key-missing'];
        }

        $status = match (true) {
            $hashValid && $sigValid  => 'verified',
            $sigValid && !$hashValid => 'tampered',   // DB row edited after signing
            default                  => 'invalid-signature',
        };

        return ['hash_valid' => $hashValid, 'sig_valid' => $sigValid, 'status' => $status];
    }

    /**
     * Walk the full chain and verify every row's previous_hash pointer.
     * Returns an array of per-row results plus an overall chain_intact flag.
     */
    public function verifyChain(): array
    {
        $rows    = CertificateLedger::orderBy('sequence')->with('clearance.user')->get();
        $results = [];
        $intact  = true;

        $expectPrev = str_repeat('0', 64);

        foreach ($rows as $row) {
            $rowResult = $this->verify($row);
            $prevOk    = hash_equals($expectPrev, $row->previous_hash);
            $rowResult['prev_ok']   = $prevOk;
            $rowResult['sequence']  = $row->sequence;
            $rowResult['reference'] = 'Clearance #' . $row->clearance_id
                . ' — ' . ($row->clearance->user->student_id ?? $row->clearance->user->name ?? '?');

            if (! $prevOk || $rowResult['status'] !== 'verified') {
                $intact = false;
            }

            $expectPrev  = $row->certificate_hash;
            $results[]   = $rowResult;
        }

        return ['chain_intact' => $intact, 'rows' => $results, 'total' => count($results)];
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function buildCanonicalJson(Clearance $clearance, string $issuedAt): string
    {
        $approvals = $clearance->approvals
            ->sortBy('department.priority')
            ->values();

        $lastApprover = $approvals
            ->where('status', 'approved')
            ->sortByDesc('reviewed_at')
            ->first();

        $referenceNo = 'MUST/'
            . strtoupper(substr($clearance->user->student_id ?? 'STU', 0, 8))
            . '/' . str_pad($clearance->id, 5, '0', STR_PAD_LEFT)
            . '/' . $clearance->submitted_at?->format('Ymd');

        $payload = [
            'reference_no'   => $referenceNo,
            'student'        => [
                'id'         => $clearance->user->id,
                'student_id' => $clearance->user->student_id ?? '',
                'name'       => $clearance->user->name,
                'programme'  => $clearance->user->programme ?? '',
                'college'    => $clearance->user->college ?? '',
            ],
            'clearance_type' => $clearance->clearance_type,
            'approval_chain' => $approvals->map(fn($a) => [
                'department'  => $a->department->name,
                'status'      => $a->status,
                'officer'     => $a->officer?->name ?? '',
                'reviewed_at' => $a->reviewed_at?->format('Y-m-d\TH:i:s\Z') ?? '',
            ])->toArray(),
            'final_approver' => $lastApprover?->officer?->name ?? '',
            'issued_at'      => $issuedAt,
        ];

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function sign(string $hash, string $previousHash, int $sequence, string $signedAt): string
    {
        $data = json_encode([
            'hash'          => $hash,
            'previous_hash' => $previousHash,
            'sequence'      => $sequence,
            'signed_at'     => $signedAt,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return hash_hmac('sha256', $data, $this->hmacKey());
    }

    private function hmacKey(): string
    {
        $key = config('app.ledger_hmac_key') ?? env('LEDGER_HMAC_KEY');

        if (empty($key)) {
            throw new RuntimeException(
                'LEDGER_HMAC_KEY is not set. ' .
                'Run: php artisan tinker --execute="echo bin2hex(random_bytes(32));" ' .
                'and add LEDGER_HMAC_KEY=<output> to your .env file.'
            );
        }

        return $key;
    }
}
