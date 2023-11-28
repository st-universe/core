<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Module\Control\Render\Fragments\ColonyFragment;
use Stu\Module\Control\Render\Fragments\MessageFolderFragment;
use Stu\Module\Control\Render\Fragments\ResearchFragment;
use Stu\Module\Control\Render\Fragments\ServertimeFragment;
use Stu\Module\Control\Render\Fragments\UserFragment;
use Stu\Module\Control\Render\GameTalRenderer;
use Stu\Module\Control\Render\GameTalRendererInterface;
use Stu\Module\Control\Render\GameTwigRenderer;
use Stu\Module\Control\Render\GameTwigRendererInterface;

use function DI\autowire;
use function DI\get;

return [
    SemaphoreUtilInterface::class => autowire(SemaphoreUtil::class),
    StuTime::class => autowire(StuTime::class),
    StuRandom::class => autowire(StuRandom::class),
    StuHashInterface::class => autowire(StuHash::class),
    'renderFragments' => [
        autowire(ColonyFragment::class),
        autowire(MessageFolderFragment::class),
        autowire(ResearchFragment::class),
        autowire(ServertimeFragment::class),
        autowire(UserFragment::class),
    ],
    GameTwigRendererInterface::class => autowire(GameTwigRenderer::class),
    GameTalRendererInterface::class => autowire(GameTalRenderer::class)
        ->constructorParameter(
            'renderFragments',
            get('renderFragments')
        ),
];
