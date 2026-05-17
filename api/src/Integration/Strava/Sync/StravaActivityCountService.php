<?php

namespace App\Integration\Strava\Sync;

use App\Entity\AthleteExternalAccount;
use App\Enum\ActivitySource;
use App\Integration\Strava\Api\StravaApiClient;
use App\Integration\Strava\Auth\StravaTokenManager;

final readonly class StravaActivityCountService
{
    public function __construct(private StravaApiClient    $stravaApiClient,
                                private StravaTokenManager $tokenManager)
    {
    }

    public function countActivities(AthleteExternalAccount $account,
                                    int                    $perPage,
                                    ?\DateTimeImmutable    $after = null,
                                    ?\DateTimeImmutable    $before = null): StravaActivityCountReport
    {
        if ($account->getProvider() !== ActivitySource::Strava) {
            throw new \LogicException('External account is not a Strava account.');
        }

        $perPage = min(200, max(1, $perPage));

        $accessToken = $this->tokenManager->getValidAccessToken($account);

        $total = 0;
        $page = 1;
        $pagesFetched = 0;

        while (true) {
            $activities = $this->stravaApiClient->listAthleteActivities(
                accessToken: $accessToken,
                page: $page,
                perPage: $perPage,
                after: $after,
                before: $before,
            );

            $count = count($activities);
            $total += $count;
            $pagesFetched++;

            if ($count < $perPage) {
                break;
            }

            $page++;
        }

        return new StravaActivityCountReport(
            activitiesFound: $total,
            pagesFetched: $pagesFetched,
            perPage: $perPage,
        );
    }
}
