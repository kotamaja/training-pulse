<?php

namespace App\Integration\Strava\Sync;

use App\Entity\AthleteExternalAccount;
use App\Entity\TrainingActivity;
use App\Enum\ActivitySource;
use App\Integration\Strava\Api\StravaApiClient;
use App\Integration\Strava\Auth\StravaTokenManager;
use App\Integration\Strava\Conversion\StravaActivityPayloadConverter;
use App\Integration\Strava\Conversion\StravaRouteStreamConverter;
use App\Repository\TrainingActivityRepository;
use App\Write\TrainingActivity\TrainingActivityWriteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class StravaActivitySyncService
{
    public function __construct(private StravaApiClient                       $stravaApiClient,
                                private StravaTokenManager                    $tokenManager,
                                private StravaActivityPayloadConverter        $payloadConverter,
                                private StravaRouteStreamConverter            $routeStreamConverter,
                                private TrainingActivityRepository            $trainingActivityRepository,
                                private TrainingActivityWriteServiceInterface $trainingActivityWriteService,
                                private EntityManagerInterface                $entityManager)
    {
    }

    public function syncAccount(AthleteExternalAccount $account,
                                int                    $page = 1,
                                int                    $perPage = 30,
                                ?\DateTimeImmutable    $after = null,
                                ?\DateTimeImmutable    $before = null,
                                StravaStreamSyncMode   $streamSyncMode = StravaStreamSyncMode::Missing): StravaActivitySyncReport
    {
        if ($account->getProvider() !== ActivitySource::Strava) {
            throw new \LogicException('External account is not a Strava account.');
        }

        $report = new StravaActivitySyncReport();

        $account->markSyncStarted();

        $accessToken = $this->tokenManager->getValidAccessToken($account);

        $activities = $this->stravaApiClient->listAthleteActivities(
            accessToken: $accessToken,
            page: $page,
            perPage: $perPage,
            after: $after,
            before: $before,
        );

        $report->fetched = count($activities);

        $processed = 0;

        foreach ($activities as $activityPayload) {
            try {
                $identity = $this->payloadConverter->extractIdentity($activityPayload);

                $existingActivity = $this->trainingActivityRepository->findOneByExternalIdentityForAthlete(
                    athlete: $account->getAthlete(),
                    source: $identity->source,
                    externalId: $identity->externalId,
                );

                if ($existingActivity === null) {
                    $createDto = $this->payloadConverter->toCreateDto($activityPayload);

                    if ($this->shouldFetchStreamsForNewActivity($streamSyncMode)) {

                        $streams = $this->stravaApiClient->getActivityStreams(
                            accessToken: $accessToken,
                            activityId: $identity->externalId,
                            keys: ['latlng'],
                        );

                        $this->routeStreamConverter->fillCreateDto(
                            streams: $streams,
                            dto: $createDto,
                        );

                        unset($streams);
                    }

                    $activity = $this->trainingActivityWriteService->create(
                        input: $createDto,
                        athlete: $account->getAthlete(),
                        actor: null,
                    );

                    $this->entityManager->flush();
                    $this->entityManager->detach($activity);

                    unset($activity, $createDto);

                    $report->created++;

                    continue;
                }

                $updateDto = $this->payloadConverter->toUpdateDto($activityPayload);

                if ($this->shouldFetchStreamsForExistingActivity($existingActivity, $streamSyncMode)) {
                    $streams = $this->stravaApiClient->getActivityStreams(
                        accessToken: $accessToken,
                        activityId: $identity->externalId,
                        keys: ['latlng'],
                    );

                    $this->routeStreamConverter->fillUpdateDto(
                        streams: $streams,
                        dto: $updateDto,
                    );


                    unset($streams);
                }


                $updateResult = $this->trainingActivityWriteService->update(
                    input: $updateDto,
                    trainingActivity: $existingActivity,
                    actor: null,
                );

                $this->entityManager->flush();

                $changed = $updateResult->changed;

                $this->entityManager->detach($existingActivity);

                unset($existingActivity, $updateDto,  $updateResult);

                if ($changed) {
                    $report->updated++;
                } else {
                    $report->unchanged++;
                }


            } catch (\Throwable $exception) {
                $report->addError(sprintf(
                    'Failed to sync Strava activity: %s',
                    $exception->getMessage(),
                ));
            }

            $processed++;

            if ($processed % 10 === 0) {
                gc_collect_cycles();
            }
        }

        if ($report->failed > 0) {
            $account->setLastError(sprintf(
                'Strava sync completed with %d failed activities.',
                $report->failed,
            ));
        } else {
            $account->markSyncSuccessful();
        }

        $this->entityManager->flush();

        return $report;
    }

    private function shouldFetchStreamsForExistingActivity(TrainingActivity $activity, StravaStreamSyncMode $mode): bool
    {
        return match ($mode) {
            StravaStreamSyncMode::No => false,
            StravaStreamSyncMode::Missing => !$activity->hasStreams(),
            StravaStreamSyncMode::Always => true,
        };
    }

    private function shouldFetchStreamsForNewActivity(StravaStreamSyncMode $mode): bool
    {
        return $mode !== StravaStreamSyncMode::No;
    }
}
