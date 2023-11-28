<?php

namespace Stu\Orm\Entity;

use Stu\Module\Message\Lib\ContactListModeEnum;

interface ContactInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getRecipientId(): int;

    public function getMode(): ContactListModeEnum;

    public function setMode(ContactListModeEnum $mode): ContactInterface;

    public function getComment(): string;

    public function setComment(string $comment): ContactInterface;

    public function getDate(): int;

    public function setDate(int $date): ContactInterface;

    public function getRecipient(): UserInterface;

    public function setRecipient(UserInterface $recipient): ContactInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ContactInterface;

    public function isFriendly(): bool;

    public function isEnemy(): bool;
}
