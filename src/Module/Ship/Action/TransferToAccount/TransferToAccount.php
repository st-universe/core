<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TransferToAccount;

use request;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class TransferToAccount implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRANSFER_TO_ACCOUNT';

    private ShipLoaderInterface $shipLoader;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradePostRepositoryInterface $tradePostRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private PositionCheckerInterface $positionChecker;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository,
        ShipStorageManagerInterface $shipStorageManager,
        PositionCheckerInterface $positionChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->positionChecker = $positionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        /**
         * @var TradePostInterface $tradepost
         */
        $tradepost = $this->tradePostRepository->find((int) request::indInt('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$this->positionChecker->checkPosition($ship, $tradepost->getShip())) {
            return;
        }

        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if (
            !$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, (int) $tradepost->getId())
            || time() > $this->tradeLicenseRepository->getExpiredTime((int) $tradepost->getId())
        ) {
            return;
        }

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradepost, $userId);

        if ($storageManager->getFreeStorage() <= 0) {
            $game->addInformation(_('Dein Warenkonto an diesem Posten ist voll'));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');

        $shipStorage = $ship->getStorage();

        if ($shipStorage->isEmpty()) {
            $game->addInformation(_("Keine Waren zum Transferieren vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurden keine Waren zum Transferieren ausgewählt"));
            return;
        }
        $game->addInformation(_("Es wurden folgende Waren ins Warenkonto transferiert"));

        foreach ($goods as $key => $value) {
            $commodityId = (int) $value;
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            $storage = $shipStorage[$commodityId] ?? null;
            if ($storage === null) {
                continue;
            }
            $count = $gcount[$key];

            $commodity = $storage->getCommodity();

            if ($count == "max") {
                $count = $storage->getAmount();
            } else {
                $count = (int) $count;
            }
            if ($count < 1 || $storageManager->getFreeStorage() <= 0) {
                continue;
            }
            if (!$commodity->isBeamable()) {
                $game->addInformationf(_('%s ist nicht beambar'), $commodity->getName());
                continue;
            }
            if ($commodity->isIllegal($tradepost->getTradeNetwork())) {
                $game->addInformationf(
                    _('Der Handel mit %s ist in diesem Handelsnetzwerk verboten'),
                    $commodity->getName()
                );
                continue;
            }
            $count = min($count, $storage->getAmount());
            if ($storageManager->getStorageSum() + $count > $tradepost->getStorage()) {
                $count = $tradepost->getStorage() - $storageManager->getStorageSum();
            }
            $game->addInformationf(_('%d %s'), $count, $commodity->getName());

            $count = (int) $count;
            $this->shipStorageManager->lowerStorage($ship, $commodity, $count);
            $storageManager->upperStorage((int) $value, $count);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}