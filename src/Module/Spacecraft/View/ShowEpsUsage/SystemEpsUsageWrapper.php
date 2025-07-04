<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowEpsUsage;

use Stu\Orm\Entity\SpacecraftSystem;

class SystemEpsUsageWrapper
{
    public function __construct(
        private readonly SpacecraftSystem $system,
        private readonly int $usage
    ) {}

    public function getSystem(): SpacecraftSystem
    {
        return $this->system;
    }

    public function getUsage(): int
    {
        return $this->usage;
    }
}
