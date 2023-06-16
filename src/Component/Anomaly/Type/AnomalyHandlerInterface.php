<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Stu\Orm\Entity\AnomalyInterface;

interface AnomalyHandlerInterface
{
    public function handleShipTick(AnomalyInterface $anomaly): void;
}
