<?php

namespace App\Http\Controllers;

use App\Services\LandingTrafficService;
use Illuminate\Http\JsonResponse;

class LandingTrafficController extends Controller
{
    public function stats(LandingTrafficService $traffic): JsonResponse
    {
        return response()->json($traffic->publicStats());
    }

    public function recordModalOpen(LandingTrafficService $traffic): JsonResponse
    {
        $traffic->recordModalOpen();

        return response()->json(['ok' => true]);
    }
}
