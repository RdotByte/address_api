<?php

namespace App\Services;

use App\Models\Postcode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PostcodeService
{
  const EARTH_RADIUS = 6371000; // Earth's radius in meters

  public function lookup(string $postcode, bool $internal = false)
  {
    $formattedPostcode = Postcode::formatPostcode($postcode);

    if (!$formattedPostcode) {
      return null;
    }

    return $internal
      ? Postcode::where('postcode', $formattedPostcode)->first()
      : Postcode::select(Postcode::$API_FIELDS)->where('postcode', $formattedPostcode)->first()->toArray();
  }

  public function findInRadius(string $postcode, float $radius)
  {
    $center = $this->lookup($postcode, true);

    if (!$center) {
      return collect();
    }

    return Postcode::select(array_merge(Postcode::$API_FIELDS, [
      DB::raw("
            ST_Distance_Sphere(
                location,
                POINT({$center->longitude}, {$center->latitude})
            ) as distance
        ")
    ]))
      ->whereRaw("
            ST_Distance_Sphere(
                location,
                POINT(?, ?)
            ) <= ?
        ", [$center->longitude, $center->latitude, $radius])
      ->orderBy('distance')
      ->get()->toArray();
  }

  public function calculateDistance(string $postcode1, string $postcode2)
  {
    $p1 = $this->lookup($postcode1, true);
    $p2 = $this->lookup($postcode2, true);

    if (!$p1 || !$p2) {
      return null;
    }

    $latFrom = deg2rad($p1->latitude);
    $lonFrom = deg2rad($p1->longitude);
    $latTo = deg2rad($p2->latitude);
    $lonTo = deg2rad($p2->longitude);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
      cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

    return self::EARTH_RADIUS * $angle;
  }
}
