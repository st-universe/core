<?php

namespace Stu\Orm\Entity;

use DateTimeInterface;

interface UserInvitationInterface
{
    public function getId(): int;

    public function getDate(): DateTimeInterface;

    public function setDate(DateTimeInterface $date): UserInvitationInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): UserInvitationInterface;

    public function getInvitedUserId(): ?int;

    public function setInvitedUserId(?int $userId): UserInvitationInterface;

    public function getToken(): string;

    public function setToken(string $token): UserInvitationInterface;

    public function isValid(int $ttl): bool;
}
