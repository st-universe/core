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

    public function getFieldIds(): string;

    public function setFieldIds(string $fieldIds): AstronomicalEntryInterface;

    public function isMeasured(): bool;
}
