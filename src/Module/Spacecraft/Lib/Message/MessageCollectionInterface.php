<?php

namespace Stu\Module\Spacecraft\Lib\Message;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;

interface MessageCollectionInterface extends InformationInterface
{
    public function add(MessageInterface $msg): void;

    /** @param string|array<string> $text */
    public function addMessageBy(
        string|array $text,
        ?int $recipient = null
    ): MessageInterface;

    /**
     * @return array<int>
     */
    public function getRecipientIds(): array;

    public function getInformationDump(?int $userId = null): InformationWrapper;

    public function isEmpty(): bool;
}
