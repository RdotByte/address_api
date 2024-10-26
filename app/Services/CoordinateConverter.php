<?php

namespace App\Services;

class CoordinateConverter
{
  // Constants used for OSGB36 to Lat/Lon calculations
  private const A = 6377563.396; // Airy 1830 major semi-axis in meters
  private const B = 6356256.909; // Airy 1830 minor semi-axis in meters
  private const F0 = 0.9996012717; // Scale factor on the central meridian
  private const LAT0 = 49 * M_PI / 180; // Latitude of true origin in radians
  private const LON0 = -2 * M_PI / 180; // Longitude of true origin in radians
  private const N0 = -100000; // Northing of true origin in meters
  private const E0 = 400000; // Easting of true origin in meters
  private const E2 = 1 - (self::B * self::B) / (self::A * self::A); // Ellipsoid squared eccentricity
  private const N = (self::A - self::B) / (self::A + self::B);

  /**
   * Converts OSGB36 Eastings/Northings to latitude and longitude in OSGB36.
   *
   * @param float $E Easting value
   * @param float $N Northing value
   * @return array ['lat' => float, 'lon' => float] Latitude and longitude in OSGB36
   */
  public function convertToOSGB36LatLon(float $E, float $N): array
  {
    $lat = self::LAT0;
    $M = 0;

    do {
      $lat = ($N - self::N0 - $M) / (self::A * self::F0) + $lat;
      $Ma = (1 + self::N + (5 / 4) * self::N ** 2 + (5 / 4) * self::N ** 3) * ($lat - self::LAT0);
      $Mb = (3 * self::N + 3 * self::N ** 2 + (21 / 8) * self::N ** 3) * sin($lat - self::LAT0) * cos($lat + self::LAT0);
      $Mc = ((15 / 8) * self::N ** 2 + (15 / 8) * self::N ** 3) * sin(2 * ($lat - self::LAT0)) * cos(2 * ($lat + self::LAT0));
      $Md = (35 / 24) * self::N ** 3 * sin(3 * ($lat - self::LAT0)) * cos(3 * ($lat + self::LAT0));
      $M = self::B * self::F0 * ($Ma - $Mb + $Mc - $Md);
    } while (($N - self::N0 - $M) >= 0.00001);

    $cosLat = cos($lat);
    $sinLat = sin($lat);
    $nu = self::A * self::F0 / sqrt(1 - self::E2 * $sinLat ** 2);
    $rho = self::A * self::F0 * (1 - self::E2) / pow(1 - self::E2 * $sinLat ** 2, 1.5);
    $eta2 = $nu / $rho - 1;

    $tanLat = tan($lat);
    $secLat = 1 / $cosLat;
    $dE = ($E - self::E0);

    $lat -= ($tanLat / (2 * $rho * $nu)) * $dE ** 2;
    $lat += ($tanLat / (24 * $rho * $nu ** 3)) * (5 + 3 * $tanLat ** 2 + $eta2 - 9 * $eta2 * $tanLat ** 2) * $dE ** 4;
    $lat -= ($tanLat / (720 * $rho * $nu ** 5)) * (61 + 90 * $tanLat ** 2 + 45 * $tanLat ** 4) * $dE ** 6;

    $lon = self::LON0 + ($secLat / $nu) * $dE;
    $lon -= ($secLat / (6 * $nu ** 3)) * ($nu / $rho + 2 * $tanLat ** 2) * $dE ** 3;
    $lon += ($secLat / (120 * $nu ** 5)) * (5 + 28 * $tanLat ** 2 + 24 * $tanLat ** 4) * $dE ** 5;

    return ['lat' => rad2deg($lat), 'lon' => rad2deg($lon)];
  }

  /**
   * Converts OSGB36 latitude and longitude to WGS84 latitude and longitude.
   *
   * @param float $lat Latitude in OSGB36
   * @param float $lon Longitude in OSGB36
   * @return array ['lat' => float, 'lon' => float] Latitude and longitude in WGS84
   */
  public function convertToWGS84(float $lat, float $lon): array
  {
    $lat = deg2rad($lat);
    $lon = deg2rad($lon);

    $dx = 446.448;
    $dy = -125.157;
    $dz = 542.060;
    $rotX = deg2rad(0.1502);
    $rotY = deg2rad(0.2470);
    $rotZ = deg2rad(0.8421);
    $s = 20.4894 * 10 ** -6;

    $a = 6378137.000;
    $eSq = (self::A ** 2 - self::B ** 2) / self::A ** 2;
    $nu = $a / sqrt(1 - $eSq * sin($lat) ** 2);

    $x1 = ($nu) * cos($lat) * cos($lon);
    $y1 = ($nu) * cos($lat) * sin($lon);
    $z1 = ((1 - $eSq) * $nu) * sin($lat);

    $x2 = $dx + (1 + $s) * ($x1 - $rotZ * $y1 + $rotY * $z1);
    $y2 = $dy + (1 + $s) * ($rotZ * $x1 + $y1 - $rotX * $z1);
    $z2 = $dz + (1 + $s) * (-$rotY * $x1 + $rotX * $y1 + $z1);

    $p = sqrt($x2 ** 2 + $y2 ** 2);
    $lat2 = atan2($z2, $p * (1 - $eSq));
    $lon2 = atan2($y2, $x2);

    return ['lat' => rad2deg($lat2), 'lon' => rad2deg($lon2)];
  }

  /**
   * Converts Eastings and Northings directly to WGS84 latitude and longitude.
   *
   * @param float $E Easting value
   * @param float $N Northing value
   * @return array ['lat' => float, 'lon' => float]
   */
  public function convertToWGS84FromEastingsNorthings(float $E, float $N): array
  {
    $osgb36 = $this->convertToOSGB36LatLon($E, $N);
    return $this->convertToWGS84($osgb36['lat'], $osgb36['lon']);
  }
}
