<?php

namespace App\Integration\Strava\Conversion;

use App\Enum\SportType;

final class StravaSportTypeConverter
{
    public function convert(string $stravaSportType): SportType
    {
        return match ($stravaSportType) {
            'Ride' => SportType::Cycling,
            'MountainBikeRide' => SportType::MountainBiking,
            'GravelRide' => SportType::GravelCycling,
            'VirtualRide' => SportType::IndoorCycling,

            'Rowing' => SportType::Rowing,
            'VirtualRow' => SportType::IndoorRowing,

            'NordicSki' => SportType::NordicSkiing,

            'Run' => SportType::Running,
            'Hike' => SportType::Hiking,
            'Walk' => SportType::Walking,

            'WeightTraining' => SportType::StrengthTraining,
            'Workout' => SportType::Other,

            default => SportType::Other,
        };
    }
}
