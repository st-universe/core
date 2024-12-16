<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Module\Control\Render\GameTwigRenderer;
use Stu\Module\Control\Render\GameTwigRendererInterface;

use function DI\autowire;

return [
    SemaphoreUtilInterface::class => autowire(SemaphoreUtil::class),
    StuTime::class => autowire(StuTime::class),
    StuRandom::class => autowire(StuRandom::class),
    StuHashInterface::class => autowire(StuHash::class),
    BenchmarkResultInterface::class => autowire(BenchmarkResult::class),
    GameTwigRendererInterface::class => autowire(GameTwigRenderer::class)
];
