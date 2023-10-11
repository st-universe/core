<?php

namespace Stu\Orm\Entity;

interface ShipTakeoverInterface
{
    public function getId(): int;

    public function setSourceShip(ShipInterface $ship): ShipTakeoverInterface;

    public function setTargetShip(ShipInterface $ship): ShipTakeoverInterface;

    public function getStartTurn(): int;

    public function setStartTurn(int $turn): ShipTakeoverInterface;
}
