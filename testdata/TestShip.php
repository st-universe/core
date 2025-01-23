<?php

declare(strict_types=1);

namespace Stu;

use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class TestShip extends AbstractTestData
{
    public function __construct(
        private int $userId,
        private int $layerId,
        private ?int $systemId,
        private int $x,
        private int $y
    ) {
        parent::__construct();
    }

    public function insertTestData(): Object
    {
        $shipCreator = $this->dic->get(ShipCreatorInterface::class);

        $wrapper = $shipCreator->createBy($this->userId, 6501, 2075)
            ->setLocation($this->getLocation($this->layerId, $this->systemId, $this->x, $this->y))
            ->createCrew()
            ->finishConfiguration();

        return $wrapper->get();
    }

    private function getLocation(
        int $layerId,
        ?int $systemId,
        $x,
        $y
    ): LocationInterface {

        if ($systemId === null) {
            $layerRepository = $this->dic->get(LayerRepositoryInterface::class);
            $mapRepository = $this->dic->get(MapRepositoryInterface::class);

            $layer = $layerRepository->find($layerId);
            return $mapRepository->getByCoordinates($layer, $this->x, $this->y);
        } else {
            $systemMapRepository = $this->dic->get(StarSystemMapRepositoryInterface::class);

            return $systemMapRepository->getByCoordinates($systemId, $x, $y);
        }
    }
}
