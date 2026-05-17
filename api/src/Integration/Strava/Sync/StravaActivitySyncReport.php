<?php

namespace App\Integration\Strava\Sync;

final class StravaActivitySyncReport
{
    public int $fetched = 0;
    public int $created = 0;
    public int $updated = 0;
    public int $unchanged = 0;
    public int $failed = 0;

    /**
     * @var list<string>
     */
    public array $errors = [];

    public function addError(string $message): void
    {
        $this->failed++;
        $this->errors[] = $message;
    }

    public function merge(self $other): void
    {
        $this->fetched += $other->fetched;
        $this->created += $other->created;
        $this->updated += $other->updated;
        $this->unchanged += $other->unchanged;
        $this->failed += $other->failed;

        foreach ($other->errors as $error) {
            $this->errors[] = $error;
        }
    }
}
