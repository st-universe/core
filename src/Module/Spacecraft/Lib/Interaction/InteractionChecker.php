<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Override;
use Stu\Lib\Map\EntityWithLocationInterface;

final class InteractionChecker implements InteractionCheckerInterface
{
    #[Override]
    public function checkPosition(EntityWithLocationInterface $one, EntityWithLocationInterface $other): bool
    {
        return $one->getLocation() === $other->getLocation();
    }
}
