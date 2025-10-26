<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\TholianWeb;

class SpacecraftFactory implements SpacecraftFactoryInterface
{
    #[\Override]
    public function create(SpacecraftRump $rump): Spacecraft
    {
        $spacecraft = match ($rump->getShipRumpCategory()->getType()) {
            SpacecraftTypeEnum::SHIP => new Ship(),
            SpacecraftTypeEnum::STATION => new Station(),
            SpacecraftTypeEnum::THOLIAN_WEB => new TholianWeb()
        };

        $spacecraft->setCondition(new SpacecraftCondition($spacecraft));

        return $spacecraft;
    }
}
