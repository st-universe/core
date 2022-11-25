<?php

namespace Stu\Orm\Entity;

interface ColonyDepositInterface
{
    public function getPlanetType(): PlanetTypeInterface;

    public function getCommodity(): CommodityInterface;

    public function getMinAmount(): int;

    public function getMaxAmount(): int;
}
