<?php

namespace Stu\Module\Ship\Lib\Message;

use Stu\Lib\Information\InformationWrapper;

interface MessageCollectionInterface
{
    public function add(MessageInterface $msg): void;

    /**
     * @param MessageInterface[] $messages
     */
    //TODO use InformationWrapper
    public function addMultiple(array $messages): void;

    /**
     * @return array<int>
     */
    public function getRecipientIds(): array;

    public function getInformationDump(?int $userId = null): InformationWrapper;

    public function isEmpty(): bool;
}
