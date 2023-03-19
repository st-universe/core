<?php

namespace Stu\Orm\Entity;

interface AllianceJobInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getType(): int;

    public function setType(int $type): AllianceJobInterface;

    public function getAlliance(): AllianceInterface;

    public function setAlliance(AllianceInterface $alliance): AllianceJobInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): AllianceJobInterface;
}
