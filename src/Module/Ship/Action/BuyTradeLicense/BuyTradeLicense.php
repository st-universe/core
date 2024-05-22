<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BuyTradeLicense;

use request;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowTradeMenu\ShowTradeMenu;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TradeLicenseInfoInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class BuyTradeLicense implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PAY_TRADELICENSE';

    private ShipLoaderInterface $shipLoader;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeLicenseInfoRepositoryInterface $tradeCreateLicenseRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradePostRepositoryInterface $tradePostRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipRepositoryInterface $shipRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private InteractionCheckerInterface $interactionChecker;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeLicenseInfoRepositoryInterface $tradeCreateLicenseRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipRepositoryInterface $shipRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        InteractionCheckerInterface $interactionChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeCreateLicenseRepository = $tradeCreateLicenseRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipRepository = $shipRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->interactionChecker = $interactionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTradeMenu::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::NO_AJAX, true);

        $user = $game->getUser();
        $userId = $user->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::getIntFatal('id'),
            $user->getId()
        );

        $postId = request::getIntFatal('postid');
        $targetId = request::getIntFatal('target');

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

        $userHasLicense = $this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $tradePost->getId());
        if ($userHasLicense) {
            throw new SanityCheckException('user already has a license');
        }

        $this->buyLicense($ship, $tradePost, $targetId, $licenseInfo, $user);

        $game->addInformation('Handelslizenz wurde erteilt');

        $this->privateMessageSender->send(
            $userId,
            $tradePost->getUserId(),
            sprintf(
                'Am %s wurde eine Lizenz gekauft',
                $tradePost->getName()
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE
        );
    }

    private function buyLicense(
        ShipInterface $ship,
        TradePostInterface $tradePost,
        int $targetId,
        TradeLicenseInfoInterface $licenseInfo,
        UserInterface $user
    ): void {
        $mode = request::getStringFatal('method');

        switch ($mode) {
            case 'ship':
                $this->payLicenseViaShip($ship, $tradePost, $targetId, $licenseInfo);
                break;
            case 'account':
                $this->payLicenseViaAccount($tradePost, $targetId, $licenseInfo, $user);
                break;
            default:
                return;
        }

        $this->createLicense($tradePost, $user, $licenseInfo);
    }

    private function payLicenseViaShip(
        ShipInterface $ship,
        TradePostInterface $tradePost,
        int $targetId,
        TradeLicenseInfoInterface $licenseInfo
    ): void {
        if (!$this->interactionChecker->checkPosition($ship, $tradePost->getShip())) {
            throw new SanityCheckException('ship is not at tradepost location');
        }

        $targetShip = $this->shipRepository->find($targetId);
        if ($targetShip === null || $targetShip->getUser() !== $ship->getUser()) {
            throw new SanityCheckException('target ship belongs to someone else');
        }
        if (!$this->interactionChecker->checkPosition($tradePost->getShip(), $targetShip)) {
            throw new SanityCheckException('target ship is not at tradepost location');
        }

        $commodity = $licenseInfo->getCommodity();
        $costs = $licenseInfo->getAmount();

        $storageManagerRemote = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $tradePost->getUser());
        $storage = $targetShip->getStorage()[$commodity->getId()] ?? null;
        if ($storage === null || $storage->getAmount() < $costs) {
            throw new SanityCheckException('target ship does not have license cost stored');
        }
        $storageManagerRemote->upperStorage($commodity->getId(), $costs);
        $this->shipStorageManager->lowerStorage(
            $targetShip,
            $commodity,
            $costs
        );
    }

    private function payLicenseViaAccount(
        TradePostInterface $tradePost,
        int $targetId,
        TradeLicenseInfoInterface $licenseInfo,
        UserInterface $user
    ): void {
        $targetTradepost = $this->tradePostRepository->find($targetId);
        if ($targetTradepost === null) {
            return;
        }

        $storageManagerRemote = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $tradePost->getUser());
        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($targetTradepost, $user);

        $commodityId = $licenseInfo->getCommodity()->getId();
        $costs = $licenseInfo->getAmount();

        $stor = $storageManager->getStorage()->get($commodityId) ?? null;
        if ($stor === null) {
            throw new SanityCheckException('storage not existent');
        }
        if ($stor->getAmount() < $costs) {
            throw new SanityCheckException('storage insufficient');
        }
        if ($targetTradepost->getTradeNetwork() !== $tradePost->getTradeNetwork()) {
            throw new SanityCheckException('wrong trade network');
        }

        $storageManagerRemote->upperStorage($commodityId, $costs);
        $storageManager->lowerStorage($commodityId, $costs);
    }

    private function createLicense(
        TradePostInterface $tradePost,
        UserInterface $user,
        TradeLicenseInfoInterface $licenseInfo
    ): void {
        $license = $this->tradeLicenseRepository->prototype();
        $license->setTradePost($tradePost);
        $license->setUser($user);
        $license->setDate(time());
        $license->setExpired(time() + $licenseInfo->getDays() * TimeConstants::ONE_DAY_IN_SECONDS);

        $this->tradeLicenseRepository->save($license);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
