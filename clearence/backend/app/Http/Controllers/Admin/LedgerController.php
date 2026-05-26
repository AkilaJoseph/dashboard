<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CertificateLedger;
use App\Services\CertificateLedgerService;
use Illuminate\Http\JsonResponse;

class LedgerController extends Controller
{
    public function __construct(private CertificateLedgerService $ledger) {}

    public function index()
    {
        $entries = CertificateLedger::with('clearance.user')
            ->orderByDesc('sequence')
            ->paginate(25);

        return view('admin.ledger.index', compact('entries'));
    }

    public function verifyChain(): JsonResponse
    {
        $result = $this->ledger->verifyChain();
        return response()->json($result);
    }
}
