<?php

namespace Stu\Orm\Entity;

interface ColonyDepositMiningInterface
{
    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ColonyDepositMiningInterface;

    public function getColony(): ColonyInterface;

    public function setColony(ColonyInterface $colony): ColonyDepositMiningInterface;

    public function getCommodity(): CommodityInterface;

    public function setCommodity(CommodityInterface $commodity): ColonyDepositMiningInterface;

    public function getAmountLeft(): int;

    public function setAmountLeft(int $amountLeft): ColonyDepositMiningInterface;
}
