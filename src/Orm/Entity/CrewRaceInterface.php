<?php

namespace Stu\Orm\Entity;

interface CrewRaceInterface
{
    public function getId(): int;

    public function getFactionId(): int;

    public function setFactionId(int $factionId): CrewRaceInterface;

    public function getDescription(): string;

    public function setDescription(string $description): CrewRaceInterface;

    public function getChance(): int;

    public function setChance(int $chance): CrewRaceInterface;

    public function getMaleRatio(): int;

    public function setMaleRatio(int $maleRatio): CrewRaceInterface;

    public function getGfxPath(): string;

    public function setGfxPath(string $gfxPath): CrewRaceInterface;

    public function getFaction(): FactionInterface;

    public function setFaction(FactionInterface $faction): CrewRaceInterface;
}