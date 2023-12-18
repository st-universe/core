<?php

namespace Stu\Component\Building;

use Stu\Orm\Entity\PlanetFieldInterface;

interface BuildingManagerInterface
{
    /**
     * Activates the building on the given field
     */
    public function activate(PlanetFieldInterface $field): bool;

    /**
     * deactivates the building on the given field
     */
    public function deactivate(PlanetFieldInterface $field): void;

    /**
     * Deconstructs the building on the given field
     */
    public function remove(PlanetFieldInterface $fieldbool, bool $isDueToUpgrade = false): void;

    /**
     * Finishes the buildprocess for the building on the given field
     */
    public function finish(PlanetFieldInterface $field, bool $activate = true): ?string;
}
