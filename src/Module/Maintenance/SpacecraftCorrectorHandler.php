<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use Override;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCorrectorInterface;

final class SpacecraftCorrectorHandler implements MaintenanceHandlerInterface
{
    public function __construct(private SpacecraftCorrectorInterface $spacecraftCorrector) {}

    #[Override]
    public function handle(): void
    {
        $this->spacecraftCorrector->correctAllSpacecrafts();
    }
}
