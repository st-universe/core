<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

interface PirateRoundInterface
{
    public function getId(): int;

    public function getStart(): int;

    public function setStart(int $start): PirateRoundInterface;

    public function getEndTime(): ?int;

    public function setEndTime(?int $endTime): PirateRoundInterface;

    public function getMaxPrestige(): int;

    public function setMaxPrestige(int $maxPrestige): PirateRoundInterface;

    public function getActualPrestige(): int;

    public function setActualPrestige(int $actualPrestige): PirateRoundInterface;

    public function getFactionWinner(): ?int;

    public function setFactionWinner(?int $factionWinner): PirateRoundInterface;

    /**
     * @return Collection<int, UserPirateRoundInterface>
     */
    public function getUserPirateRounds(): Collection;
}
