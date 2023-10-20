<?php

namespace Stu\Orm\Entity;

use Stu\Component\Ship\System\ShipSystemTypeEnum;

interface RepairTaskInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): RepairTaskInterface;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): RepairTaskInterface;

    public function setFinishTime(int $finishTime): RepairTaskInterface;

    public function getSystemType(): ShipSystemTypeEnum;

    public function setSystemType(ShipSystemTypeEnum $type): RepairTaskInterface;

    public function getHealingPercentage(): int;

    public function setHealingPercentage(int $healingPercentage): RepairTaskInterface;
}
