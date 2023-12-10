<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenuPayment;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowTradeMenuPayment implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU_CHOOSE_PAYMENT';

    private ShipLoaderInterface $shipLoader;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeLicenseInfoRepositoryInterface $TradeLicenseInfoRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradePostRepositoryInterface $tradePostRepository;

    private StorageRepositoryInterface $storageRepository;

    private ShipRepositoryInterface $shipRepository;

    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeLicenseInfoRepositoryInterface $TradeLicenseInfoRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository,
        StorageRepositoryInterface $storageRepository,
        ShipRepositoryInterface $shipRepository,
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->TradeLicenseInfoRepository = $TradeLicenseInfoRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
        $this->storageRepository = $storageRepository;
        $this->shipRepository = $shipRepository;
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );
        $game->showMacro('html/shipmacros.xhtml/entity_not_available');

        $tradepost = $this->tradePostRepository->find(request::getIntFatal('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!InteractionChecker::canInteractWith($ship, $tradepost->getShip(), $game)) {
            return;
        }
        $licenseInfo = $this->TradeLicenseInfoRepository->getLatestLicenseInfo($tradepost->getId());
        $commodityId = $licenseInfo->getCommodityId();
        $commodity = $this->commodityRepository->find($commodityId);
        $commodityName = $commodity->getName();
        $licenseCost = $licenseInfo->getAmount();

        $game->showMacro('html/shipmacros.xhtml/trademenupayment');

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountTal($tradepost, $userId));
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('LICENSECOMMODITY', $commodityId);
        $game->setTemplateVar('LICENSECOMMODITYNAME', $commodityName);
        $game->setTemplateVar('LICENSECOST', $licenseCost);
        $game->setTemplateVar('LICENSEDAYS', $licenseInfo->getDays());

        if (
            !$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $tradepost->getId())
        ) {
            $game->setTemplateVar(
                'DOCKED_SHIPS_FOR_LICENSE',
                $this->shipRepository->getWithTradeLicensePayment(
                    $userId,
                    $tradepost->getShipId(),
                    $commodityId,
                    $licenseCost
                )
            );

            $game->setTemplateVar(
                'ACCOUNTS_FOR_LICENSE',
                $this->storageRepository->getByTradeNetworkAndUserAndCommodityAmount(
                    $tradepost->getTradeNetwork(),
                    $userId,
                    $commodityId,
                    $licenseCost
                )
            );
        }
    }
}
