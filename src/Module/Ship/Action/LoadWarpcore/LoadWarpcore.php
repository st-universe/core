<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LoadWarpcore;

use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipStorageManagerInterface;
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

        $load = (int)request::postIntFatal('warpcoreload');

        if (request::postString('fleet')) {
            $msg = [];
            $msg[] = _('Flottenbefehl ausgeführt: Aufladung des Warpkerns');
            foreach ($ship->getFleet()->getShips() as $key => $ship) {
                if ($ship->getWarpcoreLoad() >= $ship->getWarpcoreCapacity()) {
                    continue;
                }
                $load = $this->loadWarpCore($ship, $load);
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
        $load = $this->loadWarpCore($ship, $load);
        if (!$load) {
            $game->addInformation(_('Zum Aufladen des Warpkerns werden mindestens 1 Deuterium sowie 1 Antimaterie benötigt'));
            return;
        }
        $game->addInformation(sprintf(_('Der Warpkern wurde um %d Einheiten aufgeladen'), $load));
    }

    public function loadWarpCore(ShipInterface $ship, int $count): ?int
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
        if ($ship->getWarpcoreLoad() + $count * ShipEnum::WARPCORE_LOAD > $ship->getWarpcoreCapacity()) {
            $load = $ship->getWarpcoreCapacity() - $ship->getWarpcoreLoad();
        } else {
            $load = $count * ShipEnum::WARPCORE_LOAD;
        }

        $commodityAmount = (int) ceil($load / ShipEnum::WARPCORE_LOAD);

        $this->shipStorageManager->lowerStorage(
            $ship,
            $shipStorage[CommodityTypeEnum::GOOD_DEUTERIUM]->getCommodity(),
            $commodityAmount
        );
        $this->shipStorageManager->lowerStorage(
            $ship,
            $shipStorage[CommodityTypeEnum::GOOD_ANTIMATTER]->getCommodity(),
            $commodityAmount
        );

        $ship->setWarpcoreLoad($ship->getWarpcoreLoad() + $load);

        $this->shipRepository->save($ship);

        return $load;
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
