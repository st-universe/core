<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

use Stu\Orm\Entity\Spacecraft;

class SpacecraftSystemWrapperFactory implements SpacecraftSystemWrapperFactoryInterface
{
    #[\Override]
    public function create(Spacecraft $spacecraft, SpacecraftSystemTypeEnum $type): ?SpacecraftSystemWrapper
    {
        if (!$spacecraft->hasSpacecraftSystem($type)) {
            return null;
        }

        return new SpacecraftSystemWrapper($spacecraft, $type);
    }
}
