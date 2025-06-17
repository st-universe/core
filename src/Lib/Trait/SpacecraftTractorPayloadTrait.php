<?php

declare(strict_types=1);

namespace Stu\Lib\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\SpacecraftInterface;

trait SpacecraftTractorPayloadTrait
{
    /**
     * proportional to tractor beam system status
     */
    protected function getTractorPayload(SpacecraftInterface $spacecraft): int
    {
        if (!$spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)) {
            return 0;
        }

        return (int) (ceil($spacecraft->getRump()->getTractorPayload()
            * $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->getStatus() / 100));
    }
}
