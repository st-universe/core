<?php

namespace Stu\Orm\Entity;

interface GameTurnInterface
{
    public function getId(): int;

    public function getTurn(): int;

    public function setTurn(int $turn): GameTurnInterface;

    public function getStart(): int;

    public function setStart(int $start): GameTurnInterface;

    public function getEnd(): int;

    public function setEnd(int $end): GameTurnInterface;

    public function getStats(): ?GameTurnStatsInterface;

    public function getPirateFleets(): ?int;

    public function setPirateFleets(int $pirateFleets): GameTurnInterface;
}
