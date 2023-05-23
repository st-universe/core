<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Module\Control\Render\Fragments\ColonyFragment;
use Stu\Module\Control\Render\Fragments\MessageFolderFragment;
use Stu\Module\Control\Render\Fragments\ResearchFragment;
use Stu\Module\Control\Render\GameTalRenderer;
use Stu\Module\Control\Render\GameTalRendererInterface;

use function DI\autowire;

return [
    SemaphoreUtilInterface::class => autowire(SemaphoreUtil::class),
    StuTime::class => autowire(StuTime::class),
    StuRandom::class => autowire(StuRandom::class),
    StuHashInterface::class => autowire(StuHash::class),
    GameTalRendererInterface::class => autowire(GameTalRenderer::class)
        ->constructorParameter(
            'renderFragments',
            [
                autowire(ResearchFragment::class),
                autowire(MessageFolderFragment::class),
                autowire(ColonyFragment::class),
            ]
        ),
];
