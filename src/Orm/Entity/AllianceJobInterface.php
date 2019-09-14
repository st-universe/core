<?php

namespace Stu\Orm\Entity;

use Alliance;
use User;

interface AllianceJobInterface
{
    public function getId(): int;

    public function getAllianceId(): int;

    public function setAllianceId(int $allianceId): AllianceJobInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): AllianceJobInterface;

    public function getType(): int;

    public function setType(int $type): AllianceJobInterface;

    public function getAlliance(): Alliance;

    public function getUser(): User;
}