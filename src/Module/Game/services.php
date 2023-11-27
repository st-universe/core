<?php

declare(strict_types=1);

namespace Stu\Module\Game;

use Stu\Component\Game\ModuleViewEnum;
use Stu\Module\Control\GameController;
use Stu\Module\Control\Render\Fragments\ColonyFragment;
use Stu\Module\Control\Render\Fragments\MessageFolderFragment;
use Stu\Module\Control\Render\Fragments\ResearchFragment;
use Stu\Module\Control\Render\Fragments\UserFragment;
use Stu\Module\Game\Action\SwitchView\SwitchView;
use Stu\Module\Game\Lib\Component\ComponentEnum;
use Stu\Module\Game\Lib\Component\ComponentLoader;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;
use Stu\Module\Game\Lib\View\Provider\AllianceProvider;
use Stu\Module\Game\Lib\View\Provider\ColonyListProvider;
use Stu\Module\Game\Lib\View\Provider\CommunicationProvider;
use Stu\Module\Game\Lib\View\Provider\DatabaseProvider;
use Stu\Module\Game\Lib\View\Provider\HistoryProvider;
use Stu\Module\Game\Lib\View\Provider\MaindeskProvider;
use Stu\Module\Game\Lib\View\Provider\MessageProvider;
use Stu\Module\Game\Lib\View\Provider\PlayerSettingsProvider;
use Stu\Module\Game\Lib\View\Provider\ResearchProvider;
use Stu\Module\Game\Lib\View\Provider\ShipListProvider;
use Stu\Module\Game\Lib\View\Provider\StationProvider;
use Stu\Module\Game\Lib\View\Provider\TradeProvider;
use Stu\Module\Game\Lib\View\Provider\UserProfileProvider;
use Stu\Module\Game\Lib\View\ViewComponentLoader;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Game\View\ShowComponent\ShowComponent;
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
            ModuleViewEnum::DATABASE->value => autowire(DatabaseProvider::class),
            ModuleViewEnum::HISTORY->value => autowire(HistoryProvider::class),
            ModuleViewEnum::OPTIONS->value => autowire(PlayerSettingsProvider::class),
            ModuleViewEnum::PROFILE->value => autowire(UserProfileProvider::class),
        ]
    ),
    ComponentLoaderInterface::class => autowire(ComponentLoader::class)->constructorParameter(
        'componentProviders',
        [
            ComponentEnum::PM_NAVLET->value => autowire(MessageFolderFragment::class),
            ComponentEnum::SERVERTIME_NAVLET->value => autowire(MessageFolderFragment::class),
            ComponentEnum::RESEARCH_NAVLET->value => autowire(ResearchFragment::class),
            ComponentEnum::COLONIES_NAVLET->value => autowire(ColonyFragment::class),
            ComponentEnum::USER_NAVLET->value => autowire(UserFragment::class),
        ]
    ),
    'GAME_ACTIONS' => [
        SwitchView::ACTION_IDENTIFIER => autowire(SwitchView::class)
    ],
    'GAME_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowInnerContent::VIEW_IDENTIFIER => autowire(ShowInnerContent::class),
        ShowComponent::VIEW_IDENTIFIER => autowire(ShowComponent::class),
    ]
];
