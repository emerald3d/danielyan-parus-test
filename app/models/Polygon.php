<?php

namespace Danielyan\Parus\App\Models;

use Danielyan\Parus\App\Data\PolygonData;
use Danielyan\Parus\App\Enums\Geometry;

class Polygon
{
    private string $cadastralNumber;
    private array $coordinates;
    private Geometry $type;

    public function __construct(PolygonData $data)
    {
        $this->cadastralNumber = $data->cadastralNumber;
        $this->coordinates = $data->coordinates;
        $this->type = $data->type;
    }

    public function getCadastralNumber(): string
    {
        return $this->cadastralNumber;
    }

    public function getType(): string
    {
        return $this->type->name;
    }

    public function toGeomSQL(): string
    {
        $geom = json_encode($this->coordinates);
        $geom = str_replace('[', '(', $geom);
        $geom = str_replace('],', '?', $geom);
        $geom = str_replace(']', ')', $geom);
        $geom = str_replace(',', ' ', $geom);
        $geom = str_replace('?', '),', $geom);

        $leftInnerBrackets = ['(0','(1','(2','(3','(4','(5','(6','(7','(8','(9'];
        $rightInnerBrackets = ['0)','1)','2)','3)','4)','5)','6)','7)','8)','9)'];
        $digitWithoutBracket = ['0','1','2','3','4','5','6','7','8','9'];

        $geom = str_replace($leftInnerBrackets, $digitWithoutBracket, $geom);
        $geom = str_replace($rightInnerBrackets, $digitWithoutBracket, $geom);

        return "ST_GeomFromText('" . $this->getType() . $geom . "'," . $_ENV['EPSG'] . ')';
    }

    public function toValuesSQL(): string
    {
        $cadastralNumber = explode(':', $this->cadastralNumber);
        $compositeCadastralNumber = '';
        foreach ($cadastralNumber as $cadastral) {
            $compositeCadastralNumber .= "'" . $cadastral . "',";
        }
        return $compositeCadastralNumber . $this->toGeomSQL();
    }
}