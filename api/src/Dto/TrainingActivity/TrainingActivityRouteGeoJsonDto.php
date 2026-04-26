<?php

namespace App\Dto\TrainingActivity;

final class TrainingActivityRouteGeoJsonDto
{
    public string $type = 'Feature';

    /**
     * @var array{
     *     type: 'LineString',
     *     coordinates: list<array{0: float, 1: float}>
     * }
     */
    public array $geometry;

    /**
     * @var array<string, mixed>
     */
    public array $properties = [];
}
