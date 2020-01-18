<?php

declare(strict_types=1);

namespace Stu\Component\Process;

use function DI\autowire;

return [
    BuildingJobFinishProcessInterface::class => autowire(BuildingJobFinishProcess::class),
    TerraformingJobFinishProcessInterface::class => autowire(TerraformingJobFinishProcess::class),
];
