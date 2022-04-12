<?php

namespace Stu\Orm\Entity;

interface UserLockInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserLockInterface;

    public function getRemainingTicks(): int;

    public function setRemainingTicks(int $count): UserLockInterface;

    public function getReason(): string;

    public function setReason(string $reason): UserLockInterface;
}
