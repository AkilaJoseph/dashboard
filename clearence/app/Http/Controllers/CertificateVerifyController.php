<?php

namespace App\Http\Controllers;

use App\Models\Clearance;
use App\Models\CertificateLedger;
use App\Services\CertificateLedgerService;
use Illuminate\Http\Request;

class CertificateVerifyController extends Controller
{
    public function __construct(private CertificateLedgerService $ledger) {}

    /**
     * Public verification page.
     * URL: /verify/{clearance}
     * Optional query params: seq, h (first 16 chars of hash — for display only, not used in verification)
     */
    public function show(Clearance $clearance): mixed
    {
        $entry = CertificateLedger::where('clearance_id', $clearance->id)->first();

        if (! $entry) {
            return view('certificate.verify', [
                'found'     => false,
                'clearance' => $clearance->load('user'),
            ]);
        }

        $result = $this->ledger->verify($entry);

        return view('certificate.verify', [
            'found'     => true,
            'clearance' => $clearance->load('user', 'approvals.department', 'approvals.officer'),
            'entry'     => $entry,
            'result'    => $result,
        ]);
    }
}
