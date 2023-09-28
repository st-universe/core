<?php

namespace Stu\Module\Message\Lib;

use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;

interface DistributedMessageSenderInterface
{
    public function distributeMessageCollection(
        FightMessageCollectionInterface $messageCollection,
        int $senderId = UserEnum::USER_NOONE,
        int $category = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        string $header = null
    ): void;
}
