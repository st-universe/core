<?php

namespace Stu\Component\Building;

use Stu\Orm\Entity\PlanetFieldInterface;

interface BuildingManagerInterface
{
    public function activate(PlanetFieldInterface $field): void;

    public function deactivate(PlanetFieldInterface $field): void;

    public function remove(
        PlanetFieldInterface $field,
        bool $upgrade = false
    ): void;

    public function finish(PlanetFieldInterface $field, bool $activate = true): void;
}
