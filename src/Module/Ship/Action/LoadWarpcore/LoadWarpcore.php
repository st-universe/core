<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LoadWarpcore;

use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\WarpcoreUtilInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LoadWarpcore implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LOAD_WARPCORE';

    private ShipLoaderInterface $shipLoader;

    private WarpcoreUtilInterface $warpcoreUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        WarpcoreUtilInterface $warpcoreUtil,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->warpcoreUtil = $warpcoreUtil;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $requestedLoad = (int) request::postIntFatal('warpcoreload');

        if (request::postString('fleet')) {
            $msg = [];
            $msg[] = _('Flottenbefehl ausgeführt: Aufladung des Warpkerns');
            foreach ($ship->getFleet()->getShips() as $ship) {
                if (!$ship->hasEnoughCrew()) {
                    $game->addInformation(sprintf(
                        _('%s: Nicht genügend Crew vorhanden'),
                        $ship->getName()
                    ));
                    continue;
                }
                if ($ship->getWarpcoreLoad() >= $ship->getWarpcoreCapacity()) {
                    continue;
                }

                if ($this->warpcoreUtil->storageContainsNeededCommodities($ship->getStorage())) {
                    $loadMessage = $this->warpcoreUtil->loadWarpCore($ship, $requestedLoad);

                    if ($loadMessage !== null) {
                        $game->addInformation($loadMessage);
                    }
                } else {
                    $game->addInformation(sprintf(
                        _('%s: Es werden mindestens folgende Waren zum Aufladen des Warpkerns benötigt:'),
                        $ship->getName()
                    ));
                    foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
                        $game->addInformation(sprintf(_('%d %s'), $loadCost, CommodityTypeEnum::getDescription($commodityId)));
                    }
                    continue;
                }
            }
            $game->addInformationMerge($msg);
            return;
        }
        if (!$ship->hasEnoughCrew()) {
            $game->addInformation(_('Nicht genügend Crew vorhanden'));
            return;
        }
        if ($ship->getWarpcoreLoad() >= $ship->getWarpcoreCapacity()) {
            $game->addInformation(_('Der Warpkern ist bereits vollständig geladen'));
            return;
        }
        if ($this->warpcoreUtil->storageContainsNeededCommodities($ship->getStorage())) {
            $loadMessage = $this->warpcoreUtil->loadWarpCore($ship, $requestedLoad);

            if ($loadMessage !== null) {
                $game->addInformation($loadMessage);
            }
        } else {
            $game->addInformation(
                _('Es werden mindestens folgende Waren zum Aufladen des Warpkerns benötigt:')
            );
            foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
                $game->addInformation(sprintf(_('%d %s'), $loadCost, CommodityTypeEnum::getDescription($commodityId)));
            }
            return;
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
