<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TransferFromAccount;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use TradePost;

final class TransferFromAccount implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRANSFER_FROM_ACCOUNT';

    private $shipLoader;

    private $tradeLicenseRepository;

    private $tradeLibFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeLibFactoryInterface $tradeLibFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeLibFactory = $tradeLibFactory;
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
         * @var TradePost $tradepost
         */
        $tradepost = ResourceCache()->getObject('tradepost', request::postIntFatal('postid'));

        if (!checkPosition($ship, $tradepost->getShip())) {
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
        if (!$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, (int) $tradepost->getId())) {
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradepost, $userId);
        $curGoods = $storageManager->getStorage();

        if ($curGoods === []) {
            $game->addInformation(_("Keine Waren zum Transferieren vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurde keine Waren zum Transferieren ausgewählt"));
            return;
        }

        $game->addInformation(_("Es wurden folgende Waren vom Warenkonto transferiert"));
        foreach ($goods as $key => $value) {
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            if (!array_key_exists($value, $curGoods)) {
                continue;
            }
            $count = $gcount[$key];
            if ($count == "m") {
                $count = (int) $curGoods[$value]->getAmount();
            } else {
                $count = (int) $count;
            }
            if ($count < 1 || $ship->getStorageSum() >= $ship->getMaxStorage()) {
                continue;
            }
            if (!$curGoods[$value]->getGood()->isBeamable()) {
                $game->addInformation($curGoods[$value]->getGood()->getName() . " ist nicht beambar");
                continue;
            }
            if ($curGoods[$value]->getGood()->isIllegal($tradepost->getTradeNetwork())) {
                $game->addInformation($curGoods[$value]->getGood()->getName() . ' ist in diesem Handelsnetzwerk illegal und kann nicht gehandelt werden');
                continue;
            }
            if ($count > $curGoods[$value]->getAmount()) {
                $count = (int) $curGoods[$value]->getAmount();
            }
            if ($ship->getStorageSum() + $count > $ship->getMaxStorage()) {
                $count = $ship->getMaxStorage() - $ship->getStorageSum();
            }
            $storageManager->lowerStorage((int) $value, (int) $count);

            $ship->upperStorage((int) $value, $count);
            $ship->setStorageSum($ship->getStorageSum() + $count);

            $game->addInformation($count . " " . $curGoods[$value]->getGood()->getName());
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
