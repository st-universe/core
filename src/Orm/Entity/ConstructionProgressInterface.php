<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface ConstructionProgressInterface
{
    public function getId(): int;

    public function getStation(): StationInterface;

    public function setStation(StationInterface $station): ConstructionProgressInterface;

    /**
     * @return Collection<int, ConstructionProgressModuleInterface>
     */
    public function getSpecialModules(): Collection;

    public function getRemainingTicks(): int;

    public function setRemainingTicks(int $remainingTicks): ConstructionProgressInterface;
}
