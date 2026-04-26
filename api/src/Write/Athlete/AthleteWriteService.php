<?php

namespace App\Write\Athlete;

use App\Dto\Athlete\AthleteCreateDto;
use App\Dto\Athlete\AthletePatchDto;
use App\Entity\Athlete;
use App\Entity\User;
use App\Write\Exception\BusinessRuleViolationException;
use Doctrine\ORM\EntityManagerInterface;

final readonly class AthleteWriteService implements AthleteWriteServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(AthleteCreateDto $input, User $user, ?User $actor = null): Athlete
    {
        if ($user->getAthlete() !== null) {
            throw new BusinessRuleViolationException(
                message: 'This user already has an athlete profile.',
                field: 'user',
            );
        }

        $displayName = $this->normalizeRequiredString(
            value: $input->displayName,
            field: 'displayName',
        );

        $this->validateHeartRates(
            restingHeartRate: $input->restingHeartRate,
            maxHeartRate: $input->maxHeartRate,
        );

        $athlete = new Athlete(
            user: $user,
            displayName: $displayName,
        );

        $athlete->setBirthYear($input->birthYear);
        $athlete->setHeightCm($input->heightCm);
        $athlete->setWeightKg($input->weightKg);
        $athlete->setRestingHeartRate($input->restingHeartRate);
        $athlete->setMaxHeartRate($input->maxHeartRate);
        $athlete->setFtpWatts($input->ftpWatts);

        $this->entityManager->persist($athlete);

        return $athlete;
    }

    public function patch(AthletePatchDto $input, Athlete $athlete, ?User $actor = null): AthletePatchResult
    {
        $changed = false;

        if ($input->isDisplayNameProvided()) {
            $displayName = $input->getDisplayName();

            if ($displayName === null || trim($displayName) === '') {
                throw new BusinessRuleViolationException(
                    message: 'Display name cannot be empty.',
                    field: 'displayName',
                );
            }

            $displayName = trim($displayName);

            if ($athlete->getDisplayName() !== $displayName) {
                $athlete->setDisplayName($displayName);
                $changed = true;
            }
        }

        if ($input->isBirthYearProvided()) {
            if ($athlete->getBirthYear() !== $input->getBirthYear()) {
                $athlete->setBirthYear($input->getBirthYear());
                $changed = true;
            }
        }

        if ($input->isHeightCmProvided()) {
            if ($athlete->getHeightCm() !== $input->getHeightCm()) {
                $athlete->setHeightCm($input->getHeightCm());
                $changed = true;
            }
        }

        if ($input->isWeightKgProvided()) {
            if ($athlete->getWeightKg() !== $input->getWeightKg()) {
                $athlete->setWeightKg($input->getWeightKg());
                $changed = true;
            }
        }

        $newRestingHeartRate = $input->isRestingHeartRateProvided()
            ? $input->getRestingHeartRate()
            : $athlete->getRestingHeartRate();

        $newMaxHeartRate = $input->isMaxHeartRateProvided()
            ? $input->getMaxHeartRate()
            : $athlete->getMaxHeartRate();

        $this->validateHeartRates(
            restingHeartRate: $newRestingHeartRate,
            maxHeartRate: $newMaxHeartRate,
        );

        if ($input->isRestingHeartRateProvided()) {
            if ($athlete->getRestingHeartRate() !== $input->getRestingHeartRate()) {
                $athlete->setRestingHeartRate($input->getRestingHeartRate());
                $changed = true;
            }
        }

        if ($input->isMaxHeartRateProvided()) {
            if ($athlete->getMaxHeartRate() !== $input->getMaxHeartRate()) {
                $athlete->setMaxHeartRate($input->getMaxHeartRate());
                $changed = true;
            }
        }

        if ($input->isFtpWattsProvided()) {
            if ($athlete->getFtpWatts() !== $input->getFtpWatts()) {
                $athlete->setFtpWatts($input->getFtpWatts());
                $changed = true;
            }
        }

        return new AthletePatchResult(
            athlete: $athlete,
            changed: $changed,
        );
    }

    public function delete(Athlete $athlete, ?User $actor = null): void
    {
        if ($actor !== null && $athlete->getUser() === $actor) {
            throw new BusinessRuleViolationException(
                message: 'You cannot delete your own athlete profile.',
            );
        }

        $this->entityManager->remove($athlete);
    }

    private function normalizeRequiredString(string $value, string $field): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new BusinessRuleViolationException(
                message: 'Value cannot be empty.',
                field: $field,
            );
        }

        return $value;
    }

    private function validateHeartRates(?int $restingHeartRate, ?int $maxHeartRate): void
    {
        if ($restingHeartRate !== null && $maxHeartRate !== null && $restingHeartRate >= $maxHeartRate) {
            throw new BusinessRuleViolationException(
                message: 'Resting heart rate must be lower than max heart rate.',
                field: 'restingHeartRate',
            );
        }
    }
}
