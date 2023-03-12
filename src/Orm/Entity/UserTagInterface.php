<?php

namespace Stu\Orm\Entity;

use DateTimeInterface;

interface UserTagInterface
{
    public function getId(): int;

    public function setTagTypeId(int $tagTypeId): UserTagInterface;

    public function getTagTypeId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserTagInterface;

    public function getDate(): ?DateTimeInterface;

    public function setDate(DateTimeInterface $date): UserTagInterface;
}
