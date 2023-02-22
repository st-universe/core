<?php

namespace Stu\Orm\Entity;

interface IgnoreListInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getRecipientId(): int;

    public function getDate(): int;

    public function setDate(int $date): IgnoreListInterface;

    public function getRecipient(): UserInterface;

    public function setRecipient(UserInterface $recipient): IgnoreListInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): IgnoreListInterface;
}
