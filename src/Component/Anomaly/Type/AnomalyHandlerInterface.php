<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Stu\Orm\Entity\AnomalyInterface;

interface AnomalyHandlerInterface
{
    public function checkForCreation(): void;

    public function handleSpacecraftTick(AnomalyInterface $anomaly): void;

    public function letAnomalyDisappear(AnomalyInterface $anomaly): void;
}
