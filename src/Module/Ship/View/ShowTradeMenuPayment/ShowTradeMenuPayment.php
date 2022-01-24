<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenuPayment;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;

final class ShowTradeMenuPayment implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU_CHOOSE_PAYMENT';

    private ShipLoaderInterface $shipLoader;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradePostRepositoryInterface $tradePostRepository;

    private TradeStorageRepositoryInterface $tradeStorageRepository;

    private ShipRepositoryInterface $shipRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository,
        ShipRepositoryInterface $shipRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
        $this->shipRepository = $shipRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        if ($userId === 126) {
            $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
        }

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $game->setMacro('html/shipmacros.xhtml/entity_not_available');

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

        $game->showMacro('html/shipmacros.xhtml/trademenupayment');

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountTal($tradepost, $userId));
        $game->setTemplateVar('SHIP', $ship);

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, (int) $tradepost->getId())) {
            $licenseCostGood = $tradepost->getLicenceCostGood();
            $licenseCost = $tradepost->calculateLicenceCost();

            $game->setTemplateVar(
                'DOCKED_SHIPS_FOR_LICENSE',
                $this->shipRepository->getWithTradeLicensePayment(
                    $userId,
                    $tradepost->getShipId(),
                    $licenseCostGood->getId(),
                    $licenseCost
                )
            );
            $foo = $this->tradeStorageRepository->getByTradeNetworkAndUserAndCommodityAmount(
                $tradepost->getTradeNetwork(),
                $userId,
                $licenseCostGood->getId(),
                $licenseCost
            );

            $this->loggerUtil->log(print_r($foo, true));

            $game->setTemplateVar(
                'ACCOUNTS_FOR_LICENSE',
                $foo
            );
        }
    }
}
