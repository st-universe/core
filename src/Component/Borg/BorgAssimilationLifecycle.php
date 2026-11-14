<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Closure;
use Stu\Orm\Entity\Spacecraft;

final class BorgAssimilationLifecycle
{
    /** @param Closure(): BorgAssimilationService $service */
    public function __construct(private Closure $service) {}

    public function releaseForSource(Spacecraft $source, bool $rewardDataNode = true): void
    {
        ($this->service)()->releaseForSource($source, $rewardDataNode);
    }
}
