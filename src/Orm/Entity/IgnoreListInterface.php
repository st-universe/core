<?php

namespace Stu\Orm\Entity;

use User;

interface IgnoreListInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function setUserId(int $userId): IgnoreListInterface;

    public function getRecipientId(): int;

    public function setRecipientId(int $recipientId): IgnoreListInterface;

    public function getDate(): int;

    public function setDate(int $date): IgnoreListInterface;

    public function getRecipient(): User;

    public function getUser(): User;
}