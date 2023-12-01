<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use RuntimeException;
use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

//TODO unit tests
final class AdventDoorHandler implements AnomalyHandlerInterface
{
    private const ADVENT_DOOR_AMOUNT = 5;

    private MapRepositoryInterface $mapRepository;

    private AnomalyCreationInterface $anomalyCreation;

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        AnomalyCreationInterface $anomalyCreation,
        StorageRepositoryInterface $storageRepository
    ) {
        $this->mapRepository = $mapRepository;
        $this->anomalyCreation = $anomalyCreation;
        $this->storageRepository = $storageRepository;
    }

    /** create advent door anomalies on first of december */
    public function checkForCreation(): void
    {
        $hour = (int)date("G");
        $day = (int)date("j");
        $month = (int)date("n");

        if ($month !== 12 || $day !== 1 || $hour !== 0) {
            return;
        }

        $this->storageRepository->truncateByCommodity(CommodityTypeEnum::COMMODITY_ADVENT_POINT);

        for ($i = 0; $i < self::ADVENT_DOOR_AMOUNT; $i++) {
            $subspaceEllipses[] = $this->anomalyCreation->create(
                AnomalyTypeEnum::SPECIAL_ADVENT_DOOR,
                $this->getRandomMap()
            );
        }
    }

    /** reset location */
    public function handleShipTick(AnomalyInterface $anomaly): void
    {
        $hour = (int)date("G");
        if ($hour !== 0) {
            return;
        }

        $anomaly->setMap($this->getRandomMap());
    }

    private function getRandomMap(): MapInterface
    {
        $randomMapId = $this->mapRepository->getRandomPassableUnoccupiedWithoutDamage();
        $map = $this->mapRepository->find($randomMapId);
        if ($map === null) {
            throw new RuntimeException('this should not happen');
        }

        return $map;
    }

    public function letAnomalyDisappear(AnomalyInterface $anomaly): void
    {
        //TODO
    }
}
