<?php

namespace Stu\Orm\Entity;

interface ColonyDepositInterface
{
    public function getColonyClass(): ColonyClassInterface;

    public function getCommodity(): CommodityInterface;

    public function getMinAmount(): int;

    public function getMaxAmount(): int;
}
