<?php

namespace Stu\Orm\Entity;

interface AstronomicalEntryInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): AstronomicalEntryInterface;

    public function getState(): int;

    public function setState(int $state): AstronomicalEntryInterface;

    public function getAstroStartTurn(): ?int;

    public function setAstroStartTurn(?int $turn): AstronomicalEntryInterface;

    public function getSystem(): ?StarSystemInterface;

    public function setSystem(StarSystemInterface $starSystem): AstronomicalEntryInterface;

    public function getRegion(): ?MapRegionInterface;

    public function setRegion(MapRegionInterface $region): AstronomicalEntryInterface;

    public function getStarsystemMap1(): ?StarSystemMapInterface;

    public function setStarsystemMap1(?StarSystemMapInterface $map): AstronomicalEntryInterface;

    public function getStarsystemMap2(): ?StarSystemMapInterface;

    public function setStarsystemMap2(?StarSystemMapInterface $map): AstronomicalEntryInterface;

    public function getStarsystemMap3(): ?StarSystemMapInterface;

    public function setStarsystemMap3(?StarSystemMapInterface $map): AstronomicalEntryInterface;

    public function getStarsystemMap4(): ?StarSystemMapInterface;

    public function setStarsystemMap4(?StarSystemMapInterface $map): AstronomicalEntryInterface;

    public function getStarsystemMap5(): ?StarSystemMapInterface;

    public function setStarsystemMap5(?StarSystemMapInterface $map): AstronomicalEntryInterface;

    public function getRegionFields(): ?string;

    public function setRegionFields(?string $regionfields): AstronomicalEntryInterface;

    public function isMeasured(): bool;
}
