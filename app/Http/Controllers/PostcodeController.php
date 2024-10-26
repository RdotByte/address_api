<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PostcodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache; // Import Cache facade

class PostcodeController extends Controller
{
  private $postcodeService;
  private const CACHE_DURATION = 1440;
  public function __construct(PostcodeService $postcodeService)
  {
    $this->postcodeService = $postcodeService;
  }

  public function lookup(Request $request)
  {
    $request->validate([
      'postcode' => 'required|string|max:8'
    ]);

    $postcode = $request->postcode;
    $result = Cache::remember("postcode_lookup_{$postcode}", self::CACHE_DURATION, function () use ($postcode) {
      return $this->postcodeService->lookup($postcode);
    });

    if (!$result) {
      return response()->json([
        'success' => false,
        'message' => 'Postcode not found'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'data' => $result
    ]);
  }

  public function radius(Request $request)
  {
    $request->validate([
      'postcode' => 'required|string|max:8',
      'radius' => 'required|numeric|min:1|max:50000'
    ]);

    $postcode = $request->postcode;
    $radius = $request->radius;

    $results = Cache::remember("postcode_radius_{$postcode}_{$radius}", self::CACHE_DURATION, function () use ($postcode, $radius) {
      return $this->postcodeService->findInRadius($postcode, $radius);
    });

    return response()->json([
      'success' => true,
      'data' => $results
    ]);
  }

  public function distance(Request $request)
  {
    $request->validate([
      'from' => 'required|string|max:8',
      'to' => 'required|string|max:8'
    ]);

    $from = $request->from;
    $to = $request->to;

    $distance = Cache::remember("postcode_distance_{$from}_{$to}", self::CACHE_DURATION, function () use ($from, $to) {
      return $this->postcodeService->calculateDistance($from, $to);
    });

    if ($distance === null) {
      return response()->json([
        'success' => false,
        'message' => 'One or both postcodes not found'
      ], 404);
    }

    return response()->json([
      'success' => true,
      'data' => [
        'distance' => round($distance, 2),
        'unit' => 'meters'
      ]
    ]);
  }
}
