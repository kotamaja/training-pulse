<?php

namespace App\Enum;

enum ActivitySource: string
{
    case Strava = 'strava';
    case Garmin = 'garmin';
    case Polar = 'polar';
    case Manual = 'manual';
    case FileImport = 'file_import';
}
