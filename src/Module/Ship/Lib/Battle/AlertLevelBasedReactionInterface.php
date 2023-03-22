<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface AlertLevelBasedReactionInterface
{
    /**
     * @return array<string>
     */
    public function react(ShipWrapperInterface $wrapper): array;
}
