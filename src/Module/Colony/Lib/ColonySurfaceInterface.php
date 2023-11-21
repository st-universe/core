<?php

namespace Stu\Module\Colony\Lib;

use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

interface ColonySurfaceInterface
{
    /**
     * @return array<int, PlanetFieldInterface>
     */
    public function getSurface(): array;

    public function getSurfaceTileStyle(): string;

    public function updateSurface(): void;

    public function hasShipyard(): bool;

    public function hasAirfield(): bool;

    public function getPopulation(): ColonyPopulationCalculatorInterface;
}
