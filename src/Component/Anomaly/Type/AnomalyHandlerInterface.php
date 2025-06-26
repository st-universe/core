<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Anomaly;

interface AnomalyHandlerInterface
{
    public function checkForCreation(): void;

    public function handleSpacecraftTick(Anomaly $anomaly): void;

    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, Anomaly $anomaly, MessageCollectionInterface $messages): void;

    public function letAnomalyDisappear(Anomaly $anomaly): void;
}
