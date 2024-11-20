<?php

declare(strict_types=1);

namespace Stu;

use Stu\Component\Map\MapEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

class TestShip extends AbstractTestData
{
    public function __construct(
        private int $layerId,
        private $x,
        private $y
    ) {}

    public function insertTestData(): int
    {
        $shipCreator = $this->dic->get(ShipCreatorInterface::class);
        $layerRepository = $this->dic->get(LayerRepositoryInterface::class);
        $mapRepository = $this->dic->get(MapRepositoryInterface::class);

        $layer = $layerRepository->find(MapEnum::DEFAULT_LAYER);
        $map = $mapRepository->getByCoordinates($layer, $this->x, $this->y);

        $wrapper = $shipCreator->createBy(1, 2, 3)
            ->setLocation($map)
            ->createCrew()
            ->finishConfiguration();

        return $wrapper->get()->getId();
    }
}
