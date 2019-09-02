<?php

namespace Stu\Orm\Entity;

use User;

interface UserProfileVisitorInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $userId): UserProfileVisitorInterface;

    public function getProfileUserId(): int;

    public function setProfileUserId(int $profileUserId): UserProfileVisitorInterface;

    public function getDate(): int;

    public function setDate(int $date): UserProfileVisitorInterface;

    public function getUser(): User;

    public function getProfileUser(): User;
}