<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenuPayment;

use AccessViolation;
use request;
use Ship;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use TradeStorage;

final class ShowTradeMenuPayment implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU_CHOOSE_PAYMENT';

    private $shipLoader;

    private $tradeLicenseRepository;

    private $tradeLibFactory;

    private $tradePostRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        /**
         * @var TradePostInterface $tradepost
         */
        $tradepost = $this->tradePostRepository->find((int) request::getIntFatal('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$ship->canInteractWith($tradepost->getShip())) {
            throw new AccessViolation();
        }

        $game->showMacro('html/shipmacros.xhtml/trademenupayment');

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountTal($tradepost, $userId));
        $game->setTemplateVar('SHIP', $ship);

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, (int) $tradepost->getId())) {
            $licenseCostGood = $tradepost->getLicenceCostGood();
            $licenseCost = $tradepost->calculateLicenceCost();

            $game->setTemplateVar(
                'DOCKED_SHIPS_FOR_LICENSE',
                Ship::getObjectsBy(
                    'WHERE user_id=' . $userId . ' AND dock=' . $tradepost->getShipId() . ' AND id IN (SELECT ships_id FROM stu_ships_storage WHERE goods_id=' . $licenseCostGood->getId() . ' AND count>=' . $licenseCost . ')'
                )
            );
            $game->setTemplateVar(
                'ACCOUNTS_FOR_LICENSE',
                TradeStorage::getAccountsByGood(
                    $licenseCostGood->getId(),
                    $userId,
                    $licenseCost,
                    $tradepost->getTradeNetwork()
                )
            );
        }
    }
}
