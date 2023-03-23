<?php

namespace Stu\Module\Ship\Lib\Battle\Message;

interface FightMessageCollectionInterface
{
    public function add(FightMessageInterface $msg): void;

    /**
     * @param FightMessageInterface[] $messages
     */
    public function addMultiple(array $messages): void;

    /**
     * @return array<int>
     */
    public function getRecipientIds(): array;

    /**
     * @return array<string>
     */
    public function getMessageDump(?int $userId = null): array;
}
