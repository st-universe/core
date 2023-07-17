<?php

namespace Stu\Module\Ship\Lib\Battle\Message;

use Stu\Lib\InformationWrapper;

interface FightMessageCollectionInterface
{
    public function add(FightMessageInterface $msg): void;

    /**
     * @param FightMessageInterface[] $messages
     */
    //TODO use InformationWrapper
    public function addMultiple(array $messages): void;

    /**
     * @return array<int>
     */
    public function getRecipientIds(): array;

    public function getInformationDump(?int $userId = null): InformationWrapper;
}
