<?php

namespace Stu\Orm\Entity;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;

interface RepairTaskInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): RepairTaskInterface;

    public function getSpacecraft(): SpacecraftInterface;

    public function setSpacecraft(SpacecraftInterface $spacecraft): RepairTaskInterface;

    public function setFinishTime(int $finishTime): RepairTaskInterface;

    public function getSystemType(): SpacecraftSystemTypeEnum;

    public function setSystemType(SpacecraftSystemTypeEnum $type): RepairTaskInterface;

    public function getHealingPercentage(): int;

    public function setHealingPercentage(int $healingPercentage): RepairTaskInterface;
}
