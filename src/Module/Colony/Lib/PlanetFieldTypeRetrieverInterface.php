<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\PlanetFieldInterface;

interface PlanetFieldTypeRetrieverInterface
{
    public function getDescription(int $fieldTypeId): string;

    public function getCategory(int $fieldTypeId): int;

    /**
     * Returns `true` if the field is located within the underground
     */
    public function isUndergroundField(
        PlanetFieldInterface $planetField
    ): bool;

    /**
     * Returns `true` if the field is located within the orbit
     */
    public function isOrbitField(
        PlanetFieldInterface $planetField
    ): bool;
}
