<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly;

use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface AnomalyHandlingInterface
{
    public function processExistingAnomalies(): void;

    public function createNewAnomalies(): void;

    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void;
}
