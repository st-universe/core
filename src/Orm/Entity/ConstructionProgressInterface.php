<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface ConstructionProgressInterface
{
    public function getId(): int;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): ConstructionProgressInterface;

    /**
     * @return ConstructionProgressModule[]|Collection
     */
    public function getSpecialModules(): Collection;

    public function getRemainingTicks(): int;

    public function setRemainingTicks(int $remainingTicks): ConstructionProgressInterface;
}
