<?php

declare(strict_types=1);

namespace Stu\Lib\Colony;

use Stu\Orm\Entity\PlanetField;

final class PlanetFieldImage
{
    public static function getPictureType(PlanetField $field): string
    {
        $rotation = $field->hasBuilding() ? $field->getRotation() : 'UNDEFINED';
        $buildingState = $field->isUnderConstruction() ? 'b' : 'a';

        return $field->getBuildingId() . '/' . $rotation . $buildingState;
    }
}
