<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Lib\Map\EntityWithLocationInterface;

interface InteractionCheckerInterface
{
    public function checkPosition(EntityWithLocationInterface $one, EntityWithLocationInterface $other): bool;
}
