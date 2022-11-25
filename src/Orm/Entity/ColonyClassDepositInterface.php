<?php

namespace Stu\Orm\Entity;

interface ColonyClassDepositInterface
{
    public function getColonyClass(): ColonyClassInterface;

    public function getCommodity(): CommodityInterface;

    public function getMinAmount(): int;

    public function getMaxAmount(): int;
}
