<?php

namespace Stu\Orm\Entity;

interface ShipTakeoverInterface
{
    public function getId(): int;

    public function setSourceSpacecraft(SpacecraftInterface $spacecraft): ShipTakeoverInterface;

    public function getSourceSpacecraft(): SpacecraftInterface;

    public function setTargetSpacecraft(SpacecraftInterface $spacecraft): ShipTakeoverInterface;

    public function getTargetSpacecraft(): SpacecraftInterface;

    public function getStartTurn(): int;

    public function setStartTurn(int $turn): ShipTakeoverInterface;

    public function getPrestige(): int;

    public function setPrestige(int $prestige): ShipTakeoverInterface;
}
