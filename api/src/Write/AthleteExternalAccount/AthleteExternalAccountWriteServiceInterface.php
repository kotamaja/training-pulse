<?php

namespace App\Write\AthleteExternalAccount;

use App\Entity\Athlete;
use App\Entity\AthleteExternalAccount;
use App\Enum\ActivitySource;

interface AthleteExternalAccountWriteServiceInterface
{
    /**
     * @param list<string> $scopes
     */
    public function connectOrUpdate(Athlete            $athlete,
                                    ActivitySource     $provider,
                                    string             $providerAccountId,
                                    string             $accessToken,
                                    string             $refreshToken,
                                    \DateTimeImmutable $expiresAt,
                                    array              $scopes = [],
                                    ?string            $displayName = null): AthleteExternalAccount;
}
