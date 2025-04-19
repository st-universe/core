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
final class EasterEggHandler implements AnomalyHandlerInterface
{
    private const int EASTER_EGG_AMOUNT = 5;

    public function __construct(
        private  MapRepositoryInterface $mapRepository,
        private LayerRepositoryInterface $layerRepository,
        private AnomalyCreationInterface $anomalyCreation,
        private StorageRepositoryInterface $storageRepository
    ) {}

    /** create easter egg anomalies on easter sunday */
    #[Override]
    public function checkForCreation(): void
    {
        $hour = (int)date("G");
        $today = date("Y-m-d");
        $easterSunday = date("Y-m-d", easter_date());

        if ($today !== $easterSunday || $hour !== 0) {
            return;
        }

        $this->storageRepository->truncateByCommodity(CommodityTypeEnum::COMMODITY_EASTER_EGG);

        for ($i = 0; $i < self::EASTER_EGG_AMOUNT; $i++) {
            $subspaceEllipses[] = $this->anomalyCreation->create(
                AnomalyTypeEnum::SPECIAL_EASTER_EGG,
                $this->getRandomMap()
            );
        }
    }


    /** reset location */
    #[Override]
    public function handleSpacecraftTick(AnomalyInterface $anomaly): void
    {

        $anomaly->setLocation($this->getRandomMap());
    }

    private function getRandomMap(): MapInterface
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
