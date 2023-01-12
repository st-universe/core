<?php

namespace Stu\Module\Ship\Lib\Battle;

interface FightMessageCollectionInterface
{
    public function add(FightMessageInterface $msg): void;

    public function getRecipientIds(): array;

    public function getMessageDump(?int $recipientId = null): array;
}
