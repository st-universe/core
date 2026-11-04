<?php

declare(strict_types=1);

namespace Stu\Component\Research;

enum SpecialResearchStatusEnum: string
{
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
