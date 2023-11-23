<?php

declare(strict_types=1);

namespace Stu\Module\Game;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameController;
use Stu\Module\Game\Action\SwitchInnerContent\SwitchInnerContent;
use Stu\Module\Game\Lib\Component\ColonyListProvider;
use Stu\Module\Game\Lib\Component\MaindeskProvider;
use Stu\Module\Game\Lib\Component\ShipListProvider;
use Stu\Module\Game\Lib\ViewComponentLoader;
use Stu\Module\Game\Lib\ViewComponentLoaderInterface;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Game\View\ShowInnerContent\ShowInnerContent;

use function DI\autowire;

return [
    ViewComponentLoaderInterface::class => autowire(ViewComponentLoader::class)->constructorParameter(
        'viewComponentProviders',
        [
            ModuleViewEnum::MAINDESK->value => autowire(MaindeskProvider::class),
            ModuleViewEnum::COLONY->value => autowire(ColonyListProvider::class),
            ModuleViewEnum::SHIP->value => autowire(ShipListProvider::class),
        ]
    ),
    'GAME_ACTIONS' => [
        SwitchInnerContent::ACTION_IDENTIFIER => autowire(SwitchInnerContent::class)
    ],
    'GAME_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowInnerContent::VIEW_IDENTIFIER => autowire(ShowInnerContent::class),
    ]
];
