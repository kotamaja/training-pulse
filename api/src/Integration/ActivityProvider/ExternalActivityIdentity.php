<?php

namespace App\Integration\ActivityProvider;

use App\Enum\ActivitySource;

final readonly class ExternalActivityIdentity
{
    public function __construct(
        public ActivitySource $source,
        public string $externalId,
    ) {
    }
}
