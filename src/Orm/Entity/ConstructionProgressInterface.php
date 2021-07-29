<?php

namespace Stu\Orm\Entity;

interface ConstructionProgressInterface
{
    public function getId(): int;

    public function setShipId(int $shipId): ConstructionProgressInterface;

    public function getRemainingTicks(): int;

    public function setRemainingTicks(int $remainingTicks): ConstructionProgressInterface;
}
