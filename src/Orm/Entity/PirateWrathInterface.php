<?php

namespace Stu\Orm\Entity;

interface PirateWrathInterface
{
    public function getUser(): UserInterface;

    public function getWrath(): int;

    public function getProtectionTimeout(): ?int;
}
