<?php

namespace Stu\Orm\Entity;

interface PrestigeLogInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $userId): PrestigeLogInterface;

    public function getAmount(): int;

    public function setAmount(int $amount): PrestigeLogInterface;

    public function getDescription(): string;

    public function setDescription(string $description): PrestigeLogInterface;

    public function setDate(int $date): PrestigeLogInterface;

    public function getDate(): int;
}
