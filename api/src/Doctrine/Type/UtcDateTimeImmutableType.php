<?php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;

final class UtcDateTimeImmutableType extends DateTimeImmutableType
{
    public const NAME = 'utc_datetime_immutable';

    private const DATABASE_FORMAT = 'Y-m-d H:i:s';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof \DateTimeImmutable) {
            throw new ConversionException(sprintf(
                'Could not convert PHP value of type "%s" to database type "%s". Expected "%s".',
                get_debug_type($value),
                self::NAME,
                \DateTimeImmutable::class,
            ));
        }

        return $value
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format(self::DATABASE_FORMAT);
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?\DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value->setTimezone(new \DateTimeZone('UTC'));
        }

        if ($value instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($value)
                ->setTimezone(new \DateTimeZone('UTC'));
        }

        if (!is_string($value)) {
            throw new ConversionException(sprintf(
                'Could not convert database value of type "%s" to PHP type "%s". Expected string or "%s".',
                get_debug_type($value),
                \DateTimeImmutable::class,
                \DateTimeInterface::class,
            ));
        }

        $date = \DateTimeImmutable::createFromFormat(
            '!' . self::DATABASE_FORMAT,
            $value,
            new \DateTimeZone('UTC'),
        );

        if (!$date instanceof \DateTimeImmutable) {
            throw new ConversionException(sprintf(
                'Could not convert database value "%s" to PHP type "%s". Expected format "%s".',
                $value,
                \DateTimeImmutable::class,
                self::DATABASE_FORMAT,
            ));
        }

        return $date;
    }


}
