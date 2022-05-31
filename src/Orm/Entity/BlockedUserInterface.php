<?php

namespace Stu\Orm\Entity;

interface BlockedUserInterface
{
    public function getId(): int;

    public function setId(int $userId): BlockedUserInterface;

    public function getTime(): int;

    public function setTime(int $time): BlockedUserInterface;

    public function getEmail(): string;

    public function setEmail(string $email): BlockedUserInterface;

    public function getMobile(): string;

    public function setMobile(string $mobile): BlockedUserInterface;
}
