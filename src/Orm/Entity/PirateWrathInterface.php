<?php

namespace Stu\Orm\Entity;

interface PirateWrathInterface
{
    public const DEFAULT_WRATH = 1000;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): PirateWrathInterface;

    public function getWrath(): int;

    public function setWrath(int $wrath): PirateWrathInterface;

    public function getProtectionTimeout(): ?int;

    public function setProtectionTimeout(?int $timestamp): PirateWrathInterface;
}
