<?php

namespace Stu\Orm\Entity;

interface BlockedUserInterface
{
    public function getId(): int;

    public function setId(int $userId): BlockedUserInterface;

    public function getTime(): int;

    public function setTime(int $time): BlockedUserInterface;

    public function getEmailHash(): string;

    public function setEmailHash(string $emailHash): BlockedUserInterface;

    public function getMobileHash(): ?string;

    public function setMobileHash(?string $mobileHash): BlockedUserInterface;
}
