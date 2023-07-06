<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Stu\Orm\Entity\AnomalyInterface;

final class SubspaceEllipseHandler implements AnomalyHandlerInterface
{
    public const STATE_DISABLED = 0;

    public function __construct()
    {
    }

    public function handleShipTick(AnomalyInterface $anomaly): void
    {
    }
}
