<?php

namespace App\Integration\Strava;

use App\Entity\AthleteExternalAccount;
use App\Enum\ActivitySource;
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
                                ?\DateTimeImmutable    $before = null): StravaActivitySyncReport
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

                    $streams = $this->stravaApiClient->getActivityStreams(
                        accessToken: $accessToken,
                        activityId: $identity->externalId,
                        keys: ['latlng'],
                    );

                    $this->routeStreamConverter->fillCreateDto(
                        streams: $streams,
                        dto: $createDto,
                    );

                    $this->trainingActivityWriteService->create(
                        input: $createDto,
                        athlete: $account->getAthlete(),
                        actor: null,
                    );

                    $report->created++;

                    continue;
                }

                $updateDto = $this->payloadConverter->toUpdateDto($activityPayload);

                if (!$existingActivity->hasRoute()) {
                    $streams = $this->stravaApiClient->getActivityStreams(
                        accessToken: $accessToken,
                        activityId: $identity->externalId,
                        keys: ['latlng'],
                    );

                    $this->routeStreamConverter->fillUpdateDto(
                        streams: $streams,
                        dto: $updateDto,
                    );
                }


                $updateResult = $this->trainingActivityWriteService->update(
                    input: $updateDto,
                    trainingActivity: $existingActivity,
                    actor: null,
                );

                if ($updateResult->changed) {
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
}
