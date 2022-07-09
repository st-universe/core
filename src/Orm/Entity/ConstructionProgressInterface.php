<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface ConstructionProgressInterface
{
    public function getId(): int;

    public function getShipId(): int;

    public function setShipId(int $shipId): ConstructionProgressInterface;

    /**
     * @return ConstructionProgressModule[]
     */
    public function getSpecialModules(): Collection;

    public function getRemainingTicks(): int;

    public function setRemainingTicks(int $remainingTicks): ConstructionProgressInterface;
}
