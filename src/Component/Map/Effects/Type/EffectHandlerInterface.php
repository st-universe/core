<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;

interface EffectHandlerInterface
{
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void;

    public function addFlightInformation(Location $location, MessageCollectionInterface $messages): void;

    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void;
}
