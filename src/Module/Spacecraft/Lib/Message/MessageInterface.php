<?php

namespace Stu\Module\Spacecraft\Lib\Message;

use Stu\Lib\Information\InformationInterface;

interface MessageInterface extends InformationInterface
{
    public function getSenderId(): int;

    public function getRecipientId(): ?int;

    /**
     * @return array<string>
     */
    public function getMessage(): array;

    public function add(?string $msg): void;

    /**
     * @param array<string> $msg
     */
    public function addMessageMerge(array $msg): void;

    public function isEmpty(): bool;
}
