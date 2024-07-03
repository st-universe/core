<?php

declare(strict_types=1);

namespace Stu\Module\Message\Lib;

use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;

final class DistributedMessageSender implements DistributedMessageSenderInterface
{
    public function __construct(private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
    public function distributeMessageCollection(
        MessageCollectionInterface $messageCollection,
        int $senderId = UserEnum::USER_NOONE,
        PrivateMessageFolderTypeEnum $folderType = PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
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
                $folderType
            );
        }
    }
}
