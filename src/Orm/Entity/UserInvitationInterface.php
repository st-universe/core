<?php

namespace Stu\Orm\Entity;

use DateTimeInterface;

interface UserInvitationInterface
{
    public function getId(): int;

    public function getDate(): DateTimeInterface;

    public function setDate(DateTimeInterface $date): UserInvitationInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserInvitationInterface;

    public function getInvitedUser(): ?UserInterface;

    public function setInvitedUser(?UserInterface $user): UserInvitationInterface;

    public function getToken(): string;

    public function setToken(string $token): UserInvitationInterface;
}
