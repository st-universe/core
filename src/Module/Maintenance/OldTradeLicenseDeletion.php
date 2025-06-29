<?php

namespace Stu\Module\Maintenance;

use Override;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class OldTradeLicenseDeletion implements MaintenanceHandlerInterface
{
    private const int INFORM_ABOUT_ALMOST_EXPIRED_IN_DAYS = 7;

    public function __construct(
        private TradeLicenseRepositoryInterface $tradeLicenseRepository,
        private TradeLicenseInfoRepositoryInterface $tradeLicenseInfoRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private StuTime $stuTime
    ) {}

    #[Override]
    public function handle(): void
    {
        $deletedLicenses = $this->deleteExpiredLicenses();
        $this->informAboutAlmostExpiredLicenses($deletedLicenses);
    }

    /** @param array<int, TradeLicense> $deletedLicenses */
    private function informAboutAlmostExpiredLicenses(array $deletedLicenses): void
    {
        $almostExpiredLicenses = $this->tradeLicenseRepository->getLicensesExpiredInLessThan(self::INFORM_ABOUT_ALMOST_EXPIRED_IN_DAYS);

        foreach ($almostExpiredLicenses as $license) {
            //skip just deleted licenses
            if (array_key_exists($license->getId(), $deletedLicenses)) {
                continue;
            }

            $latestLicenseInfo = $license->getTradePost()->getLatestLicenseInfo();

            // send message to user
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $license->getUser()->getId(),
                sprintf(
                    "Deine Lizenz am Handelsposten %s läuft in weniger als %d Tage(n) ab.%s",
                    $license->getTradePost()->getName(),
                    $license->getRemainingFullDays($this->stuTime) + 1,
                    $latestLicenseInfo !== null ? sprintf(
                        "\nEine neue Lizenz für %d Tage kostet aktuell %d %s.",
                        $latestLicenseInfo->getDays(),
                        $latestLicenseInfo->getAmount(),
                        $latestLicenseInfo->getCommodity()->getName()
                    ) : ''
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
            );
        }
    }

    /** @return array<int, TradeLicense> */
    private function deleteExpiredLicenses(): array
    {
        $licensesToDelete = $this->tradeLicenseRepository->getExpiredLicenses();

        foreach ($licensesToDelete as $license) {
            $latestLicenseInfo = $this->tradeLicenseInfoRepository->getLatestLicenseInfo($license->getTradePostId());

            $userId = $license->getUser()->getId();
            $tradePost = $license->getTradePost();

            $userHasLicense = $this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $tradePost->getId());
            if (!$userHasLicense) {

                // send message to user
                $this->privateMessageSender->send(
                    UserEnum::USER_NOONE,
                    $userId,
                    sprintf(
                        "Deine Lizenz am Handelsposten %s ist abgelaufen.%s",
                        $tradePost->getName(),
                        $latestLicenseInfo !== null ? sprintf(
                            "\nEine neue Lizenz für %d Tage kostet aktuell %d %s.",
                            $latestLicenseInfo->getDays(),
                            $latestLicenseInfo->getAmount(),
                            $latestLicenseInfo->getCommodity()->getName()
                        ) : ''
                    ),
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM
                );
            }

            $this->tradeLicenseRepository->delete($license);
        }

        return $licensesToDelete;
    }
}
