<?php

namespace Stu\Orm\Entity;

use User;

interface AllianceJobInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $userId): AllianceJobInterface;

    public function getType(): int;

    public function setType(int $type): AllianceJobInterface;

    public function getAlliance(): AllianceInterface;

    public function setAlliance(AllianceInterface $alliance): AllianceJobInterface;

    public function getUser(): User;
}