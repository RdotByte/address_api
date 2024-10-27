<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postcode extends Model
{
    protected $fillable = [
        'postcode',
        'postcode_trimmed',
        'latitude',
        'longitude',
        'country',
        'nhs_ha',
        'admin_county',
        'admin_district',
        'admin_ward',
        'quality',
        'constituency',
        'european_electoral_region',
        'primary_care_trust',
        'region',
        'parish',
        'lsoa',
        'msoa',
        'nuts',
        'incode',
        'outcode',
        'location',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'quality' => 'integer',
    ];

    public static array $API_FIELDS = ['postcode', 'postcode_trimmed', 'latitude', 'longitude', 'incode', 'outcode'];

    public static function formatPostcode($postcode)
    {
        $postcode = strtoupper(str_replace(' ', '', $postcode));
        $length = strlen($postcode);

        if ($length < 5 || $length > 7) {
            return null;
        }

        return substr($postcode, 0, -3) . ' ' . substr($postcode, -3);
    }
}
