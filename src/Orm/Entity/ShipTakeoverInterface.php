<?php

namespace Stu\Orm\Entity;

interface ShipTakeoverInterface
{
    public function getId(): int;

    public function setSourceShip(ShipInterface $ship): ShipTakeoverInterface;

    public function getSourceShip(): ShipInterface;

    public function setTargetShip(ShipInterface $ship): ShipTakeoverInterface;

    public function getTargetShip(): ShipInterface;

    public function getStartTurn(): int;

    public function setStartTurn(int $turn): ShipTakeoverInterface;

    public function getPrestige(): int;

    public function setPrestige(int $prestige): ShipTakeoverInterface;
}
