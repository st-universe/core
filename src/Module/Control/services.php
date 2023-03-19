<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Module\Control\Render\GameTalRenderer;
use Stu\Module\Control\Render\GameTalRendererInterface;

use function DI\autowire;

return [
    SemaphoreUtilInterface::class => autowire(SemaphoreUtil::class),
    StuTime::class => autowire(StuTime::class),
    StuHashInterface::class => autowire(StuHash::class),
    GameTalRendererInterface::class => autowire(GameTalRenderer::class)
        ->constructorParameter(
            'renderFragments',
            [
                autowire(Render\Fragments\ResearchFragment::class),
                autowire(Render\Fragments\MessageFolderFragment::class),
                autowire(Render\Fragments\ColonyFragment::class),
            ]
        ),
];
