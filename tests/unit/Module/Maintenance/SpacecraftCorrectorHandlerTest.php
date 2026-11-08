<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCorrectorInterface;
use Stu\StuTestCase;

final class SpacecraftCorrectorHandlerTest extends StuTestCase
{
    public function testMaintenanceRunsTheQualityAwareCorrectorOnce(): void
    {
        $corrector = $this->mock(SpacecraftCorrectorInterface::class);
        $corrector->shouldReceive('correctAllSpacecrafts')->once();

        new SpacecraftCorrectorHandler($corrector)->handle();
    }
}
