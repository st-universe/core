<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\GameEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class OldTradeLicenseDeletion implements MaintenanceHandlerInterface
{
    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(): void
    {
        $licensesToDelete = $this->tradeLicenseRepository->getExpiredLicenses();

        foreach ($licensesToDelete as $license) {

            // send message to user
            $this->privateMessageSender->send(
                GameEnum::USER_NOONE,
                $license->getUser()->getId(),
                sprintf(
                    'Deine Lizenz am Handelsposten %s ist abgelaufen',
                    $license->getTradePost()->getName()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
            );

            $this->tradeLicenseRepository->delete($license);
        }
    }
}
