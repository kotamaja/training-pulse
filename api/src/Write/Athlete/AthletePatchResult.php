<?php

namespace App\Write\Athlete;

use App\Entity\Athlete;

final readonly class AthletePatchResult
{
    public function __construct(
        public Athlete $athlete,
        public bool $changed,
    ) {
    }
}
