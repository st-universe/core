<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenuPayment;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeCreateLicenceRepositoryInterface;

final class ShowTradeMenuPayment implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU_CHOOSE_PAYMENT';

    private ShipLoaderInterface $shipLoader;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeCreateLicenceRepositoryInterface $tradeCreateLicenceRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradePostRepositoryInterface $tradePostRepository;

    private TradeStorageRepositoryInterface $tradeStorageRepository;

    private ShipRepositoryInterface $shipRepository;

    private CommodityRepositoryInterface $commodityRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeCreateLicenceRepositoryInterface $tradeCreateLicenceRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository,
        ShipRepositoryInterface $shipRepository,
        CommodityRepositoryInterface $commodityRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeCreateLicenceRepository = $tradeCreateLicenceRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
        $this->shipRepository = $shipRepository;
        $this->commodityRepository = $commodityRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $game->showMacro('html/shipmacros.xhtml/entity_not_available');

        /**
         * @var TradePostInterface $tradepost
         */
        $tradepost = $this->tradePostRepository->find((int) request::getIntFatal('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$ship->canInteractWith($tradepost->getShip())) {
            return;
        }
        $licenseInfo = $this->tradeCreateLicenceRepository->getLatestLicenseInfo($tradepost->getId());
        $commodityId = $licenseInfo->getGoodsId();
        $commodity = $this->commodityRepository->find($commodityId);
        $commodityName = $commodity->getName();
        $licenseCost = $licenseInfo->getAmount();

        $game->showMacro('html/shipmacros.xhtml/trademenupayment');

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountTal($tradepost, $userId));
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('LICENSEGOOD', $commodityId);
        $game->setTemplateVar('LICENSEGOODNAME', $commodityName);
        $game->setTemplateVar('LICENSECOST', $licenseCost);
        $game->setTemplateVar('LICENSEDAYS', $licenseInfo->getDays());

        if (
            !$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, (int) $tradepost->getId())
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
                $this->tradeStorageRepository->getByTradeNetworkAndUserAndCommodityAmount(
                    $tradepost->getTradeNetwork(),
                    $userId,
                    $commodityId,
                    $licenseCost
                )
            );
        }
    }
}