<?php

namespace Stu\Module\Message\Lib;

use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;

interface DistributedMessageSenderInterface
{
    public function distributeMessageCollection(
        MessageCollectionInterface $messageCollection,
        int $senderId = UserConstants::USER_NOONE,
        PrivateMessageFolderTypeEnum $folderType = PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
        ?string $header = null,
        bool $sendDumpToEveryone = false
    ): void;
}
