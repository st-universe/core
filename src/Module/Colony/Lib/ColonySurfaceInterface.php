<?php

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\PlanetField;

interface ColonySurfaceInterface
{
    /**
     * @return array<int, PlanetField>
     */
    public function getSurface(): array;

    public function getSurfaceTileStyle(): string;

    public function updateSurface(): void;

    public function hasShipyard(): bool;

    public function hasAirfield(): bool;
}
