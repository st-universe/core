<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use Stu\Module\Colony\Lib\ColonyCorrectorInterface;

final class ColonyCorrectorHandler implements MaintenanceHandlerInterface
{
    private ColonyCorrectorInterface $colonyCorrector;

    public function __construct(
        ColonyCorrectorInterface $colonyCorrector
    ) {
        $this->colonyCorrector = $colonyCorrector;
    }

    public function handle(): void
    {
        $this->colonyCorrector->correct(false);
    }
}
