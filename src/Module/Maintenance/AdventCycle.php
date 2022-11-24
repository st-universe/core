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
    private const ADVENT_DOOR_AMOUNT = 5;

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
        $day = (int)date("j");
        $month = (int)date("n");

        if ($month < 12) {
            return;
        }

        if ($day === 1) {

            //reset all existing advent points on first of december
            $this->storageRepository->truncateByCommodity(CommodityTypeEnum::COMMODITY_ADVENT_POINT);

            //create advent door and set random location
            $adventDoors = $this->createAdventDoors();
            $this->setRandomLocation($adventDoors);
        } else if ($day < 25) {

            //set new random advent door location
            $this->setRandomLocation($this->getCurrentAdventDoors());
        } else {

            //remove advent door
            foreach ($this->getCurrentAdventDoors() as $adventDoor) {
                $this->shipRemover->remove($adventDoor);
            }
        }
    }

    /**
     * @return ShipInterface[]
     */
    private function createAdventDoors(): array
    {
        $result = [];

        $plan = $this->shipBuildplanRepository->getAdventDoorBuildplan();

        for ($i = 0; $i < self::ADVENT_DOOR_AMOUNT; $i++) {
            $adventDoor = $this->shipCreator->createBy(GameEnum::USER_NOONE, $plan->getRump()->getId(), $plan->getId());
            $adventDoor->setEps($adventDoor->getMaxEps());
            $adventDoor->setReactorLoad($adventDoor->getReactorCapacity());
            $adventDoor->setShield($adventDoor->getMaxShield());
            $adventDoor->setEBatt($adventDoor->getMaxEBatt());

            $this->shipRepository->save($adventDoor);
            $result[] = $adventDoor;
        }

        return $result;
    }

    private function setRandomLocation(array $adventDoors): void
    {
        foreach ($adventDoors as $adventDoor) {
            if (rand(0, 1) === 0) {
                $randomMapId = $this->mapRepository->getRandomPassableUnoccupiedWithoutDamage();
                $adventDoor->updateLocation($this->mapRepository->find($randomMapId), null);
            } else {
                $randomSysMapId = $this->starSystemMapRepository->getRandomPassableUnoccupiedWithoutDamage();
                $adventDoor->updateLocation(null, $this->starSystemMapRepository->find($randomSysMapId));
            }

            $this->shipRepository->save($adventDoor);
        }
    }

    /**
     * @return ShipInterface[]
     */
    private function getCurrentAdventDoors(): array
    {
        return $this->shipBuildplanRepository->getAdventDoorBuildplan()->getShiplist()->toArray();
    }
}
