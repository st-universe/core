<?php

namespace Stu\Module\Message\Lib;

use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;

interface DistributedMessageSenderInterface
{
    public function distributeMessageCollection(
        MessageCollectionInterface $messageCollection,
        int $senderId = UserEnum::USER_NOONE,
        PrivateMessageFolderTypeEnum $folderType = PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
        ?string $header = null
    ): void;
}
