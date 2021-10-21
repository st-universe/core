<?php

namespace Stu\Orm\Entity;

use Stu\Orm\Entity\UserInterface;

interface RepairTaskInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): RepairTaskInterface;

    public function getShip(): ShipInterface;

    public function setShip(ShipInterface $ship): RepairTaskInterface;

    public function setFinishTime(int $finishTime): RepairTaskInterface;
}
