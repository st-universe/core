<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Override;
use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\Map;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

//TODO unit tests
final class AdventDoorHandler implements AnomalyHandlerInterface
{
    private const int ADVENT_DOOR_AMOUNT = 5;

    public function __construct(
        private  MapRepositoryInterface $mapRepository,
        private LayerRepositoryInterface $layerRepository,
        private AnomalyCreationInterface $anomalyCreation,
        private StorageRepositoryInterface $storageRepository
    ) {}

    /** create advent door anomalies on first of december */
    #[Override]
    public function checkForCreation(): void
    {
        $hour = (int)date("G");
        $day = (int)date("j");
        $month = (int)date("n");

        if ($month !== 12 || $day !== 1 || $hour !== 0) {
            return;
        }

        $this->storageRepository->truncateByCommodity(CommodityTypeConstants::COMMODITY_ADVENT_POINT);

        for ($i = 0; $i < self::ADVENT_DOOR_AMOUNT; $i++) {
            $subspaceEllipses[] = $this->anomalyCreation->create(
                AnomalyTypeEnum::SPECIAL_ADVENT_DOOR,
                $this->getRandomMap()
            );
        }
    }

    /** reset location */
    #[Override]
    public function handleSpacecraftTick(Anomaly $anomaly): void
    {
        $hour = (int)date("G");
        if ($hour !== 0) {
            return;
        }

        $anomaly->setLocation($this->getRandomMap());
    }

    private function getRandomMap(): Map
    {
        $layer = $this->layerRepository->getDefaultLayer();

        while (true) {
            $map = $this->mapRepository->getRandomPassableUnoccupiedWithoutDamage($layer);

            if (!$map->isAnomalyForbidden()) {
                return $map;
            }
        }
    }

    #[Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, Anomaly $anomaly, MessageCollectionInterface $messages): void
    {
        //not needed
    }

    #[Override]
    public function letAnomalyDisappear(Anomaly $anomaly): void
    {
        //TODO
    }
}
