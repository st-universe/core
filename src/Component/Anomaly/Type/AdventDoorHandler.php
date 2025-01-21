<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Override;
use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\MapInterface;
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

        $this->storageRepository->truncateByCommodity(CommodityTypeEnum::COMMODITY_ADVENT_POINT);

        for ($i = 0; $i < self::ADVENT_DOOR_AMOUNT; $i++) {
            $subspaceEllipses[] = $this->anomalyCreation->create(
                AnomalyTypeEnum::SPECIAL_ADVENT_DOOR,
                $this->getRandomMap()
            );
        }
    }

    /** reset location */
    #[Override]
    public function handleSpacecraftTick(AnomalyInterface $anomaly): void
    {
        $hour = (int)date("G");
        if ($hour !== 0) {
            return;
        }

        $anomaly->setLocation($this->getRandomMap());
    }

    private function getRandomMap(): MapInterface
    {
        $layer = $this->layerRepository->getDefaultLayer();

        return $this->mapRepository->getRandomPassableUnoccupiedWithoutDamage($layer);
    }

    #[Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, AnomalyInterface $anomaly, MessageCollectionInterface $messages): void
    {
        //not needed
    }

    #[Override]
    public function letAnomalyDisappear(AnomalyInterface $anomaly): void
    {
        //TODO
    }
}
