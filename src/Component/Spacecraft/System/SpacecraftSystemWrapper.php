<?php

namespace Stu\Component\Spacecraft\System;

use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;

class SpacecraftSystemWrapper
{
    private SpacecraftSystemInterface $spacecraftSystem;

    public function __construct(
        SpacecraftInterface $spacecraft,
        SpacecraftSystemTypeEnum $type
    ) {
        $this->spacecraftSystem = $spacecraft->getShipSystem($type);
    }

    public function get(): SpacecraftSystemInterface
    {
        return $this->spacecraftSystem;
    }

    public function isActivated(): bool
    {
        return $this->spacecraftSystem->getMode()->isActivated();
    }
}
