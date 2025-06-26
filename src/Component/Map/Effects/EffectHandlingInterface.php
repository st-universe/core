<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects;

use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Tick\Spacecraft\Handler\SpacecraftTickHandlerInterface;
use Stu\Orm\Entity\Location;

interface EffectHandlingInterface extends SpacecraftTickHandlerInterface
{
    public function addFlightInformationForActiveEffects(Location $location, MessageCollectionInterface $messages): void;

    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void;
}
