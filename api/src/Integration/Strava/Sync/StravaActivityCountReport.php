<?php

namespace App\Integration\Strava\Sync;

final readonly class StravaActivityCountReport
{
    public function __construct(public int $activitiesFound,
                                public int $pagesFetched,
                                public int $perPage)
    {
    }

    public function estimatedStreamRequests(): int
    {
        return $this->activitiesFound;
    }

    public function estimatedFullImportRequests(): int
    {
        return $this->pagesFetched + $this->estimatedStreamRequests();
    }
}
