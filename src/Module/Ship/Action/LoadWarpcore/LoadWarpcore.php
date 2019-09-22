<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LoadWarpcore;

use request;
use ShipData;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStorageManagerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class LoadWarpcore implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LOAD_WARPCORE';

    private $shipLoader;

    private $shipStorageManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStorageManagerInterface $shipStorageManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStorageManager = $shipStorageManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $load = (int)request::postIntFatal('warpcoreload');

        if (request::postString('fleet')) {
            $msg = [];
            $msg[] = _('Flottenbefehl ausgeführt: Aufladung des Warpkerns');
            foreach ($ship->getFleet()->getShips() as $key => $ship) {
                if ($ship->getWarpcoreLoad() >= $ship->getWarpcoreCapacity()) {
                    continue;
                }
                $load = $ship->loadWarpCore($load);
                if (!$load) {
                    $game->addInformation(sprintf(_('%s: Zum Aufladen des Warpkerns werden mindestens 1 Deuterium sowie 1 Antimaterie benötigt'),
                        $ship->getName()));
                    continue;
                }
                $game->addInformation(sprintf(_('%s: Der Warpkern wurde um %d Einheiten aufgeladen'), $ship->getName(),
                    $load));
            }
            $game->addInformationMerge($msg);
            return;
        }
        if ($ship->getWarpcoreLoad() >= $ship->getWarpcoreCapacity()) {
            $game->addInformation(_('Der Warpkern ist bereits vollständig geladen'));
            return;
        }
        $load = $ship->loadWarpCore($load);
        if (!$load) {
            $game->addInformation(_('Zum Aufladen des Warpkerns werden mindestens 1 Deuterium sowie 1 Antimaterie benötigt'));
            return;
        }
        $game->addInformation(sprintf(_('Der Warpkern wurde um %d Einheiten aufgeladen'), $load));
    }

    public function loadWarpCore(ShipData $ship, int $count): ?int
    {
        $shipStorage = $ship->getStorage();
        foreach ([CommodityTypeEnum::GOOD_DEUTERIUM, CommodityTypeEnum::GOOD_ANTIMATTER] as $commodityId) {
            $storage = $shipStorage[$commodityId] ?? null;
            if ($storage === null) {
                return null;
            }
            if ($storage->getAmount() < $count) {
                $count = $storage->getAmount();
            }
        }
        $this->shipStorageManager->lowerStorage(
            $ship,
            $shipStorage[CommodityTypeEnum::GOOD_DEUTERIUM]->getCommodity(),
            $count
        );
        $this->shipStorageManager->lowerStorage(
            $ship,
            $shipStorage[CommodityTypeEnum::GOOD_ANTIMATTER]->getCommodity(),
            $count
        );
        if ($ship->getWarpcoreLoad() + $count * WARPCORE_LOAD > $ship->getWarpcoreCapacity()) {
            $load = $ship->getWarpcoreCapacity() - $ship->getWarpcoreLoad();
        } else {
            $load = $count * WARPCORE_LOAD;
        }
        $ship->setWarpcoreLoad($ship->getWarpcoreLoad() + $load);
        $ship->save();

        return $load;
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
