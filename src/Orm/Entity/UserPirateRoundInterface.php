<?php

namespace Stu\Orm\Entity;

interface UserPirateRoundInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUser(UserInterface $user): UserPirateRoundInterface;

    public function getUser(): UserInterface;

    public function getPirateRoundId(): int;

    public function setPirateRound(PirateRoundInterface $pirateRound): UserPirateRoundInterface;

    public function getPirateRound(): PirateRoundInterface;

    public function getDestroyedShips(): int;

    public function setDestroyedShips(int $destroyedShips): UserPirateRoundInterface;

    public function getPrestige(): int;

    public function setPrestige(int $prestige): UserPirateRoundInterface;
}
