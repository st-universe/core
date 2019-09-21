<?php

namespace Stu\Module\Communication\Lib;

use Stu\Orm\Entity\ContactInterface;
use Stu\Orm\Entity\UserInterface;

interface PrivateMessageListItemInterface
{
    public function getSender(): UserInterface;

    public function getDate(): int;

    public function isMarkableAsNew(): bool;

    public function getText(): string;

    public function getNew(): bool;

    public function getId(): int;

    public function displayUserLinks(): bool;

    public function getReplied(): bool;

    public function senderIsIgnored(): bool;

    public function senderIsContact(): ?ContactInterface;
}