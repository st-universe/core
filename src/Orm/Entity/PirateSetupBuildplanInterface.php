<?php

namespace Stu\Orm\Entity;

interface PirateSetupBuildplanInterface
{
    public function getBuildplan(): SpacecraftBuildplanInterface;

    public function getAmount(): int;
}
