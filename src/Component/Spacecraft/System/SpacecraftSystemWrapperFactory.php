<?php

namespace Stu\Component\Spacecraft\System;

use Stu\Orm\Entity\SpacecraftInterface;

class SpacecraftSystemWrapperFactory implements SpacecraftSystemWrapperFactoryInterface
{
    public function create(SpacecraftInterface $spacecraft, SpacecraftSystemTypeEnum $type): ?SpacecraftSystemWrapper
    {
        if (!$spacecraft->hasSpacecraftSystem($type)) {
            return null;
        }

        return new SpacecraftSystemWrapper($spacecraft, $type);
    }
}
