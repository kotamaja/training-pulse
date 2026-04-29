<?php

namespace App\Integration\Support;

final class ExternalPayloadReader
{
    /**
     * @param array<string, mixed> $data
     */
    public function requireString(array $data, string $key, string $context = 'value'): string
    {
        $value = $data[$key] ?? null;

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (!is_string($value) || trim($value) === '') {
            throw new \RuntimeException(sprintf(
                'Missing or invalid %s "%s".',
                $context,
                $key,
            ));
        }

        return trim($value);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function optionalString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function stringOrDefault(array $data, string $key, string $default): string
    {
        return $this->optionalString($data, $key) ?? $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function optionalFloat(array $data, string $key): ?float
    {
        $value = $data[$key] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value) || is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function optionalInt(array $data, string $key): ?int
    {
        $value = $data[$key] ?? null;

        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', trim($value)) === 1) {
            return (int) $value;
        }

        return null;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $path
     */
    public function optionalStringPath(array $data, array $path): ?string
    {
        $value = $this->readPath($data, $path);

        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string> $path
     */
    public function readPath(array $data, array $path): mixed
    {
        $current = $data;

        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }

            $current = $current[$segment];
        }

        return $current;
    }
}
