<?php

declare(strict_types=1);

namespace Stu\Module\Game;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameController;
use Stu\Module\Game\Action\SwitchInnerContent\SwitchInnerContent;
use Stu\Module\Game\Lib\Component\AllianceProvider;
use Stu\Module\Game\Lib\Component\ColonyListProvider;
use Stu\Module\Game\Lib\Component\CommunicationProvider;
use Stu\Module\Game\Lib\Component\MaindeskProvider;
use Stu\Module\Game\Lib\Component\MessageProvider;
use Stu\Module\Game\Lib\Component\ResearchProvider;
use Stu\Module\Game\Lib\Component\ShipListProvider;
use Stu\Module\Game\Lib\Component\StationProvider;
use Stu\Module\Game\Lib\Component\TradeProvider;
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
            ModuleViewEnum::STATION->value => autowire(StationProvider::class),
            ModuleViewEnum::COMMUNICATION->value => autowire(CommunicationProvider::class),
            ModuleViewEnum::PM->value => autowire(MessageProvider::class),
            ModuleViewEnum::RESEARCH->value => autowire(ResearchProvider::class),
            ModuleViewEnum::TRADE->value => autowire(TradeProvider::class),
            ModuleViewEnum::ALLIANCE->value => autowire(AllianceProvider::class),
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