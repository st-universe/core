<?php

namespace Stu\Module\Spacecraft\Lib\Reactor;

use Stu\Component\Spacecraft\System\Data\FusionCoreSystemData;
use Stu\Component\Spacecraft\System\Data\SingularityCoreSystemData;
use Stu\Component\Spacecraft\System\Data\WarpCoreSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\ReactorWrapper;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;

class ReactorWrapperFactory implements ReactorWrapperFactoryInterface
{
    public function createReactorWrapper(SpacecraftWrapper $wrapper): ?ReactorWrapperInterface
    {
        $reactorSystemData = null;
        $spacecraft = $wrapper->get();

        if ($spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPCORE)) {
            $reactorSystemData = $wrapper->getSpecificShipSystem(
                SpacecraftSystemTypeEnum::WARPCORE,
                WarpCoreSystemData::class
            );
        }
        if ($spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SINGULARITY_REACTOR)) {
            $reactorSystemData = $wrapper->getSpecificShipSystem(
                SpacecraftSystemTypeEnum::SINGULARITY_REACTOR,
                SingularityCoreSystemData::class
            );
        }
        if ($spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::FUSION_REACTOR)) {
            $reactorSystemData = $wrapper->getSpecificShipSystem(
                SpacecraftSystemTypeEnum::FUSION_REACTOR,
                FusionCoreSystemData::class
            );
        }

        if ($reactorSystemData === null) {
            return null;
        }

        return new ReactorWrapper($wrapper, $reactorSystemData);
    }
}
