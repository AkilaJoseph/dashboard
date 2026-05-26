<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clearance;
use App\Services\PredictionService;
use Illuminate\Http\JsonResponse;

class ClearancePredictionController extends Controller
{
    public function __construct(private readonly PredictionService $predictions) {}

    public function show(Clearance $clearance): JsonResponse
    {
        if ($clearance->user_id !== auth()->id()) {
            abort(403);
        }

        $result = $this->predictions->estimateCompletion($clearance);

        return response()->json([
            'estimated_completion_at'  => $result['estimated_completion_at']?->toIso8601String(),
            'confidence_level'         => $result['confidence_level'],
            'per_department_breakdown' => $result['per_department_breakdown'],
        ]);
    }
}
