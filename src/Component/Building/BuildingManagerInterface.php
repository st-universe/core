<?php

namespace Stu\Component\Building;

use Stu\Orm\Entity\PlanetField;

interface BuildingManagerInterface
{
    /**
     * Activates the building on the given field
     */
    public function activate(PlanetField $field): bool;

    /**
     * deactivates the building on the given field
     */
    public function deactivate(PlanetField $field): void;

    /**
     * Deconstructs the building on the given field
     */
    public function remove(PlanetField $field, bool $isDueToUpgrade = false): void;

    /**
     * Finishes the buildprocess for the building on the given field
     */
    public function finish(PlanetField $field, bool $activate = true): ?string;
}
