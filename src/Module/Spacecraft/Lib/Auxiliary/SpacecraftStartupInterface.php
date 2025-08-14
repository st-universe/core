<?php

namespace Stu\Module\Spacecraft\Lib\Auxiliary;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface SpacecraftStartupInterface
{
    /** @param array<SpacecraftSystemTypeEnum> $additionalSystemTypes */
    public function startup(SpacecraftWrapperInterface $wrapper, array $additionalSystemTypes = []): void;
}
