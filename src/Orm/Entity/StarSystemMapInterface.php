<?php

namespace Stu\Orm\Entity;

interface StarSystemMapInterface extends LocationInterface
{
    public function getSx(): int;

    public function setSx(int $sx): StarSystemMapInterface;

    public function getSy(): int;

    public function setSy(int $sy): StarSystemMapInterface;

    public function getSystemId(): int;

    public function getSystem(): StarSystemInterface;

    public function setSystem(StarSystemInterface $starSystem): StarSystemMapInterface;

    public function getColony(): ?ColonyInterface;

    public function getMapRegion(): ?MapRegionInterface;

    public function getAdministratedRegion(): ?MapRegionInterface;

    public function getInfluenceArea(): ?StarSystemInterface;

    public function getFieldStyle(): string;
}
