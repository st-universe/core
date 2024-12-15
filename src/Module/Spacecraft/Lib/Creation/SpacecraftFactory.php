<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\Station;

class SpacecraftFactory implements SpacecraftFactoryInterface
{
    public function create(SpacecraftRumpInterface $rump): SpacecraftInterface
    {
        $type = $rump->getShipRumpCategory()->getType();

        return match ($type) {
            SpacecraftTypeEnum::SHIP => new Ship(),
            SpacecraftTypeEnum::STATION => new Station()
        };
    }
}
