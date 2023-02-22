<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use DateTimeInterface;

interface SessionStringInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUser(UserInterface $user): SessionStringInterface;

    public function getSessionString(): string;

    public function setSessionString(string $sessionString): SessionStringInterface;

    public function getDate(): DateTimeInterface;

    public function setDate(DateTimeInterface $date): SessionStringInterface;
}
