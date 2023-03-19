<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use DateTimeInterface;

interface UserIpTableInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): UserInterface;

    public function getIp(): string;

    public function setIp(string $ip): UserIpTableInterface;

    public function getSessionId(): string;

    public function setSessionId(string $sessionId): UserIpTableInterface;

    public function getUserAgent(): string;

    public function setUserAgent(string $userAgent): UserIpTableInterface;

    public function getStartDate(): ?DateTimeInterface;

    public function setStartDate(DateTimeInterface $startDate): UserIpTableInterface;

    public function getEndDate(): ?DateTimeInterface;

    public function setEndDate(DateTimeInterface $endDate): UserIpTableInterface;
}
