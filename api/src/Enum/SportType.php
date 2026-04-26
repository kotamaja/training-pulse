<?php

namespace App\Enum;

enum SportType: string
{
    case Cycling = 'cycling';
    case RoadCycling = 'road_cycling';
    case MountainBiking = 'mountain_biking';
    case GravelCycling = 'gravel_cycling';
    case IndoorCycling = 'indoor_cycling';

    case Rowing = 'rowing';
    case IndoorRowing = 'indoor_rowing';

    case NordicSkiing = 'nordic_skiing';
    case NordicSkiingClassic = 'nordic_skiing_classic';
    case NordicSkiingSkating = 'nordic_skiing_skating';

    case Running = 'running';
    case Hiking = 'hiking';
    case Walking = 'walking';

    case StrengthTraining = 'strength_training';
    case Mobility = 'mobility';

    case Other = 'other';
}
