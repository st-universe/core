<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use Override;
use Stu\Module\Colony\Lib\ColonyCorrectorInterface;

final class ColonyCorrectorHandler implements MaintenanceHandlerInterface
{
    public function __construct(private ColonyCorrectorInterface $colonyCorrector)
    {
    }

    #[Override]
    public function handle(): void
    {
        $this->colonyCorrector->correct(false);
    }
}
