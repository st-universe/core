<?php

namespace Stu\Orm\Entity;

interface UserProfileVisitorInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getProfileUserId(): int;

    public function getDate(): int;

    public function setDate(int $date): UserProfileVisitorInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserProfileVisitorInterface;

    public function getProfileUser(): UserInterface;

    public function setProfileUser(UserInterface $profileUser): UserProfileVisitorInterface;
}