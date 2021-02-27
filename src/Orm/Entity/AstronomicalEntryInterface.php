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

    public function getSystemId(): int;

    public function setSystemId(int $systemId): AstronomicalEntryInterface;

    public function getStarsystemMap1(): ?StarSystemMapInterface;

    public function setStarsystemMap1(?StarSystemMapInterface $starsystem_map): AstronomicalEntryInterface;

    public function getStarsystemMap2(): ?StarSystemMapInterface;

    public function setStarsystemMap2(?StarSystemMapInterface $starsystem_map): AstronomicalEntryInterface;

    public function getStarsystemMap3(): ?StarSystemMapInterface;

    public function setStarsystemMap3(?StarSystemMapInterface $starsystem_map): AstronomicalEntryInterface;

    public function getStarsystemMap4(): ?StarSystemMapInterface;

    public function setStarsystemMap4(?StarSystemMapInterface $starsystem_map): AstronomicalEntryInterface;

    public function getStarsystemMap5(): ?StarSystemMapInterface;

    public function setStarsystemMap5(?StarSystemMapInterface $starsystem_map): AstronomicalEntryInterface;
}
