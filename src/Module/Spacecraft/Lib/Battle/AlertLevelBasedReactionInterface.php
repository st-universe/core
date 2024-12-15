<?php

namespace Stu\Module\Spacecraft\Lib\Battle;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface AlertLevelBasedReactionInterface
{
    public function react(SpacecraftWrapperInterface $wrapper, InformationInterface $informations): void;
}
