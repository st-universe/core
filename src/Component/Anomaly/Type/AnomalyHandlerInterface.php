<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\AnomalyInterface;

interface AnomalyHandlerInterface
{
    public function checkForCreation(): void;

    public function handleSpacecraftTick(AnomalyInterface $anomaly): void;

    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, AnomalyInterface $anomaly, MessageCollectionInterface $messages): void;

    public function letAnomalyDisappear(AnomalyInterface $anomaly): void;
}
