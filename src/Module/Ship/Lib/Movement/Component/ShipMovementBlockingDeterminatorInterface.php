<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

interface ShipMovementBlockingDeterminatorInterface
{
    /**
     * @param array<ShipWrapperInterface> $wrappers
     *
     * @return array<string> List of error messages
     */
    public function determine(array $wrappers): array;
}
