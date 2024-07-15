<?php

namespace Stu\Orm\Entity;

interface BuoyInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $user_id): void;

    public function getText(): string;

    public function setText(string $text): void;

    public function getLocation(): LocationInterface;

    public function setLocation(LocationInterface $location): BuoyInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): void;
}
