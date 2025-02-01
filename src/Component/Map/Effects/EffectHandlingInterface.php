<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface EffectHandlingInterface
{
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void;

    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void;
}
