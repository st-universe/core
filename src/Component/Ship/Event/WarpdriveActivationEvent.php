<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Event;

use Stu\Module\Ship\Lib\ShipWrapperInterface;

/**
 * Describes the warpdrive activation event
 */
class WarpdriveActivationEvent
{
    public function __construct(private ShipWrapperInterface $wrapper) {}

    /**
     * Returns the ship wrapper, that activated its warpdrive
     */
    public function getWrapper(): ShipWrapperInterface
    {
        return $this->wrapper;
    }
}
