<?php

declare(strict_types=1);

namespace Stu\Module\Admin;

use Stu\Component\Admin\Reset\ResetManager;
use Stu\Component\Admin\Reset\ResetManagerInterface;
use function DI\autowire;

return [
    ResetManagerInterface::class => autowire(ResetManager::class)
];
