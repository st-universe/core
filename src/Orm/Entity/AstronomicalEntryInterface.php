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

    public function getSystem(): StarSystemInterface;

    public function setSystem(StarSystemInterface $starSystem): AstronomicalEntryInterface;

    public function getStarsystemMap1(): ?StarSystemMapInterface;

    public function setStarsystemMapId1(?int $id): AstronomicalEntryInterface;

    public function getStarsystemMap2(): ?StarSystemMapInterface;

    public function setStarsystemMapId2(?int $id): AstronomicalEntryInterface;

    public function getStarsystemMap3(): ?StarSystemMapInterface;

    public function setStarsystemMapId3(?int $id): AstronomicalEntryInterface;

    public function getStarsystemMap4(): ?StarSystemMapInterface;

    public function setStarsystemMapId4(?int $id): AstronomicalEntryInterface;

    public function getStarsystemMap5(): ?StarSystemMapInterface;

    public function setStarsystemMapId5(?int $id): AstronomicalEntryInterface;

    public function getStarsystemMapId1(): ?int;
    public function getStarsystemMapId2(): ?int;
    public function getStarsystemMapId3(): ?int;
    public function getStarsystemMapId4(): ?int;
    public function getStarsystemMapId5(): ?int;

    public function isMeasured(): bool;
}
