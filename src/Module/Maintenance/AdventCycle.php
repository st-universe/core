<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\GameEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class AdventCycle implements MaintenanceHandlerInterface
{
    private StorageRepositoryInterface $storageRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ShipCreatorInterface $shipCreator;

    private ShipRemoverInterface $shipRemover;

    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    public function __construct(
        StorageRepositoryInterface $storageRepository,
        ShipRepositoryInterface $shipRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ShipCreatorInterface $shipCreator,
        ShipRemoverInterface $shipRemover,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        $this->storageRepository = $storageRepository;
        $this->shipRepository = $shipRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->shipCreator = $shipCreator;
        $this->shipRemover = $shipRemover;
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
    }

    public function handle(): void
    {
        $day = (int)date("d");
        $month = (int)date("m");

        if ($month < 12) {
            //return; RESET
        }

        if ($day === 24) { //RESET TO 1

            //reset all existing advent points on first of december
            $this->storageRepository->truncateByCommodity(CommodityTypeEnum::COMMODITY_ADVENT_POINT);

            //create advent door and set random location
            $adventDoor = $this->createAdventDoor();
            $this->setRandomLocation($adventDoor);
        } else if ($day < 25) {

            //set new random advent door location
            $this->setRandomLocation($this->getCurrentAdventDoor());
        } else {

            //remove advent door
            $this->shipRemover->remove($this->getCurrentAdventDoor());
        }
    }

    private function createAdventDoor(): ShipInterface
    {
        $plan = $this->shipBuildplanRepository->getAdventDoorBuildplan();

        $adventDoor = $this->shipCreator->createBy(GameEnum::USER_NOONE, $plan->getRump()->getId(), $plan->getId());
        $adventDoor->setEps($adventDoor->getMaxEps());
        $adventDoor->setReactorLoad($adventDoor->getReactorCapacity());
        $adventDoor->setShield($adventDoor->getMaxShield());
        $adventDoor->setEBatt($adventDoor->getMaxEBatt());

        $this->shipRepository->save($adventDoor);

        return $adventDoor;
    }

    private function setRandomLocation(ShipInterface $adventDoor): void
    {
        $mapCount = $this->mapRepository->count([]);
        $systemMapCount = $this->starSystemMapRepository->count([]);

        if (rand(0, $mapCount + $systemMapCount) < $mapCount) {
            $randomMapId = $this->mapRepository->getRandomPassableUnoccupiedWithoutDamage();
            $adventDoor->setMap($this->mapRepository->find($randomMapId));
        } else {
            $randomSysMapId = $this->starSystemMapRepository->getRandomPassableUnoccupiedWithoutDamage();
            $adventDoor->setStarsystemMap($this->starSystemMapRepository->find($randomSysMapId));
        }

        $this->shipRepository->save($adventDoor);
    }

    private function getCurrentAdventDoor(): ShipInterface
    {
        return $this->shipBuildplanRepository->getAdventDoorBuildplan()->getShiplist()->first();
    }
}
