<?php

namespace App\Write\TrainingActivity;

use App\Dto\TrainingActivity\TrainingActivityCreateDto;
use App\Dto\TrainingActivity\TrainingActivityUpdateDto;
use App\Entity\Athlete;
use App\Entity\TrainingActivity;
use App\Entity\User;

interface TrainingActivityWriteServiceInterface
{
    public function create(TrainingActivityCreateDto $input,
                           Athlete                   $athlete,
                           ?User                     $actor = null): TrainingActivity;

    public function update(TrainingActivityUpdateDto $input,
                           TrainingActivity          $trainingActivity,
                           ?User                     $actor = null): TrainingActivityUpdateResult;

    public function delete(TrainingActivity $trainingActivity, ?User $actor = null): void;
}
