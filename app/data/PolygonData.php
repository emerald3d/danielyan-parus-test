<?php

namespace Danielyan\Parus\App\Data;

use Danielyan\Parus\App\Enums\Geometry;

class PolygonData
{
    readonly string $cadastralNumber;
    readonly array $coordinates;
    readonly Geometry $type;

    public function __construct(string $cadastralNumber, array $coordinates, Geometry $type)
    {
        $this->cadastralNumber = $cadastralNumber;
        $this->coordinates = $coordinates;
        $this->type = $type;
    }
}