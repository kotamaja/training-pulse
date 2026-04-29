<?php

namespace App\Write\TrainingActivity;

use App\Entity\TrainingActivity;

final readonly class TrainingActivityUpdateResult
{
    public function __construct(public TrainingActivity $trainingActivity,
                                public bool             $changed)
    {
    }
}
