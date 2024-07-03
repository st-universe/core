<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\RenewTradeLicense;

use Override;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Entity\TradeLicenseInfoInterface;
use Stu\Orm\Entity\TradeLicenseInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class RenewTradeLicense implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RENEW_TRADELICENSE';

    public function __construct(private TradeLicenseRepositoryInterface $tradeLicenseRepository, private TradeLicenseInfoRepositoryInterface $tradeCreateLicenseRepository, private TradeLibFactoryInterface $tradeLibFactory, private TradePostRepositoryInterface $tradePostRepository, private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowAccounts::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::NO_AJAX, true);

        $user = $game->getUser();
        $userId = $user->getId();

        $postId = request::postIntFatal('postid');
        if ($postId === 0) {
            throw new SanityCheckException('postid parameter missing');
        }

        $tradePost = $this->tradePostRepository->find($postId);
        if ($tradePost === null) {
            throw new SanityCheckException('tradepost does not exist');
        }
        if ($this->tradeLicenseRepository->getAmountByUser($userId) >= GameEnum::MAX_TRADELICENSE_COUNT) {
            throw new SanityCheckException('user reached trade license limit');
        }

        $licenseInfo = $this->tradeCreateLicenseRepository->getLatestLicenseInfo($tradePost->getId());
        if ($licenseInfo === null) {
            throw new SanityCheckException('tradepost has no license info');
        }

        $activeLicense = $this->tradeLicenseRepository->getLatestActiveLicenseByUserAndTradePost($userId, $tradePost->getId());

        if ($activeLicense === null) {
            throw new SanityCheckException('user does not have a license');
        }
        $this->renewLicense($activeLicense, $licenseInfo);

        $game->addInformation('Handelslizenz wurde erteilt');

        $this->privateMessageSender->send(
            $userId,
            $tradePost->getUserId(),
            sprintf(
                'Am %s wurde eine Lizenz gekauft',
                $tradePost->getName()
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE
        );
    }

    private function renewLicense(
        TradeLicenseInterface $activeLicense,
        TradeLicenseInfoInterface $licenseInfo
    ): void {
        $this->payLicenseViaAccount($activeLicense, $licenseInfo);

        //update license
        $activeLicense->setExpired(time() + $licenseInfo->getDays() * TimeConstants::ONE_DAY_IN_SECONDS);
        $this->tradeLicenseRepository->save($activeLicense);
    }

    private function payLicenseViaAccount(
        TradeLicenseInterface $activeLicense,
        TradeLicenseInfoInterface $licenseInfo,
    ): void {
        $tradePost = $activeLicense->getTradePost();

        $storageManagerRemote = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $tradePost->getUser());
        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $activeLicense->getUser());

        $commodityId = $licenseInfo->getCommodity()->getId();
        $costs = $licenseInfo->getAmount();

        $stor = $storageManager->getStorage()->get($commodityId) ?? null;
        if ($stor === null) {
            throw new SanityCheckException('storage not existent');
        }
        if ($stor->getAmount() < $costs) {
            throw new SanityCheckException('storage insufficient');
        }

        $storageManagerRemote->upperStorage($commodityId, $costs);
        $storageManager->lowerStorage($commodityId, $costs);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
