<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Spacecraft\ManagerComponent;

use Override;
use Stu\Component\Research\TechnologyProductionCompletion;

final class ProduceTechnologyCommodities implements ManagerComponentInterface
{
    public function __construct(private TechnologyProductionCompletion $production) {}

    #[Override]
    public function work(): void
    {
        $this->production->work(true);
    }
}
