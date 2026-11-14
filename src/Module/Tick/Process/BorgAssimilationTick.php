<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Component\Borg\BorgAssimilationService;

final class BorgAssimilationTick implements ProcessTickHandlerInterface
{
    public function __construct(private BorgAssimilationService $assimilation) {}

    #[Override]
    public function work(): void
    {
        $this->assimilation->process();
    }
}
