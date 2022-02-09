<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LoadWarpcore;

use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LoadWarpcore implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LOAD_WARPCORE';

    private ShipLoaderInterface $shipLoader;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStorageManagerInterface $shipStorageManager,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStorageManager = $shipStorageManager;
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
                $load = $this->loadWarpCore($ship, $requestedLoad);
                if (!$load) {
                    $game->addInformation(sprintf(
                        _('%s: Es werden mindestens folgende Waren zum Aufladen des Warpkerns benötigt:'),
                        $ship->getName()
                    ));
                    foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
                        $game->addInformation(sprintf(_('%d %s'), $loadCost, CommodityTypeEnum::getDescription($commodityId)));
                    }
                    continue;
                }
                $game->addInformation(sprintf(
                    _('%s: Der Warpkern wurde um %d Einheiten aufgeladen'),
                    $ship->getName(),
                    $load
                ));
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
        $load = $this->loadWarpCore($ship, $requestedLoad);
        if (!$load) {
            $game->addInformation(
                _('Es werden mindestens folgende Waren zum Aufladen des Warpkerns benötigt:')
            );
            foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
                $game->addInformation(sprintf(_('%d %s'), $loadCost, CommodityTypeEnum::getDescription($commodityId)));
            }
            return;
        }
        $game->addInformation(sprintf(_('Der Warpkern wurde um %d Einheiten aufgeladen'), $load));
    }

    public function loadWarpCore(ShipInterface $ship, int $count): ?int
    {
        $shipStorage = $ship->getStorage();
        foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
            $storage = $shipStorage[$commodityId] ?? null;
            if ($storage === null) {
                return null;
            }
            if ($storage->getAmount() < ($count * $loadCost)) {
                $count = (int) ($storage->getAmount() / $loadCost);
            }
        }
        if ($ship->getWarpcoreLoad() + $count * ShipEnum::WARPCORE_LOAD > $ship->getWarpcoreCapacity()) {
            $load = $ship->getWarpcoreCapacity() - $ship->getWarpcoreLoad();
        } else {
            $load = $count * ShipEnum::WARPCORE_LOAD;
        }

        $commodityAmount = (int) ceil($load / ShipEnum::WARPCORE_LOAD);

        foreach (ShipEnum::WARPCORE_LOAD_COST as $commodityId => $loadCost) {
            $this->shipStorageManager->lowerStorage(
                $ship,
                $shipStorage[$commodityId]->getCommodity(),
                $loadCost * $commodityAmount
            );
        }

        $ship->setWarpcoreLoad($ship->getWarpcoreLoad() + $load);

        $this->shipRepository->save($ship);

        return $load;
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
