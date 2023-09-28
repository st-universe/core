<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;

final class DistributedMessageSender implements DistributedMessageSenderInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->privateMessageSender = $privateMessageSender;
    }

    public function distributeMessageCollection(
        FightMessageCollectionInterface $messageCollection,
        int $senderId = UserEnum::USER_NOONE,
        int $category = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
        string $header = null
    ): void {
        foreach ($messageCollection->getRecipientIds() as $recipientId) {
            $informations = $messageCollection->getInformationDump($recipientId);

            if ($header !== null) {
                $pm = sprintf(
                    "%s\n\n%s",
                    $header,
                    $informations->getInformationsAsString()
                );
            } else {
                $pm =  $informations->getInformationsAsString();
            }

            $this->privateMessageSender->send(
                $senderId,
                $recipientId,
                $pm,
                $category
            );
        }
    }
}
