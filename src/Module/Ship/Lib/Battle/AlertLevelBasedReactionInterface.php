<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface AlertLevelBasedReactionInterface
{
    public function react(ShipWrapperInterface $wrapper, InformationInterface $informations): void;
}
