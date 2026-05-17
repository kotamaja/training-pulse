<?php

namespace App\Integration\Strava\Sync;

enum StravaStreamSyncMode: string
{
    case No = 'no';
    case Missing = 'missing';
    case Always = 'always';

}
