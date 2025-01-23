<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\Event;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

/**
 * Describes the warpdrive activation event
 */
class WarpdriveActivationEvent
{
    public function __construct(private SpacecraftWrapperInterface $wrapper) {}

    /**
     * Returns the ship wrapper, that activated its warpdrive
     */
    public function getWrapper(): SpacecraftWrapperInterface
    {
        return $this->wrapper;
    }
}
