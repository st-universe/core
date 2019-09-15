<?php

namespace Stu\Orm\Entity;

use User;

interface ContactInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $userId): ContactInterface;

    public function getRecipientId(): int;

    public function setRecipientId(int $recipientId): ContactInterface;

    public function getMode(): int;

    public function setMode(int $mode): ContactInterface;

    public function getComment(): string;

    public function setComment(string $comment): ContactInterface;

    public function getDate(): int;

    public function setDate(int $date): ContactInterface;

    public function getRecipient(): User;

    public function getUser(): User;

    public function isFriendly(): bool;

    public function isEnemy(): bool;
}