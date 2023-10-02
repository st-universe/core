<?php

namespace Stu\Module\Message\Lib;

use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Battle\Message\MessageCollectionInterface;

interface DistributedMessageSenderInterface
{
    public function distributeMessageCollection(
        MessageCollectionInterface $messageCollection,
        int $senderId = UserEnum::USER_NOONE,
        int $category = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        string $header = null
    ): void;
}
