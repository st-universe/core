<?php

declare(strict_types=1);

namespace Stu\Module\Game;

use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\GameController;
use Stu\Module\Game\Action\Logout\Logout;
use Stu\Module\Game\Action\SwitchView\SwitchView;
use Stu\Module\Game\Action\SetTutorial\SetTutorial;
use Stu\Module\Game\Action\FinishTutorial\FinishTutorial;
use Stu\Module\Game\Action\Transfer\Transfer;
use Stu\Module\Game\Component\ColoniesComponent;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Game\Component\MessageFolderComponent;
use Stu\Module\Game\Component\NagusComponent;
use Stu\Module\Game\Component\ResearchComponent;
use Stu\Module\Game\Component\ServertimeComponent;
use Stu\Module\Game\Component\UserProfileComponent;
use Stu\Module\Game\Lib\GameSetup;
use Stu\Module\Game\Lib\GameSetupInterface;
use Stu\Module\Game\Lib\View\Provider\AllianceProvider;
use Stu\Module\Game\Lib\View\Provider\ColonyListProvider;
use Stu\Module\Game\Lib\View\Provider\CommunicationProvider;
use Stu\Module\Game\Lib\View\Provider\DatabaseProvider;
use Stu\Module\Game\Lib\View\Provider\HistoryProvider;
use Stu\Module\Game\Lib\View\Provider\MaindeskProvider;
use Stu\Module\Game\Lib\View\Provider\MapProvider;
use Stu\Module\Game\Lib\View\Provider\Message\ClassicStyleProvider;
use Stu\Module\Game\Lib\View\Provider\Message\MessageProvider;
use Stu\Module\Game\Lib\View\Provider\Message\MessengerStyleProvider;
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
use Stu\Module\Game\View\ShowPadd\ShowPadd;
use Stu\Module\Game\View\ShowTutorialCloseButton\ShowTutorialCloseButton;
use Stu\Module\Game\View\Noop\Noop;
use Stu\Module\Game\View\ShowTransfer\ShowTransfer;

use function DI\autowire;

return [
    GameSetupInterface::class => autowire(GameSetup::class),
    ClassicStyleProvider::class => autowire(ClassicStyleProvider::class),
    MessengerStyleProvider::class => autowire(MessengerStyleProvider::class),
    ViewComponentLoaderInterface::class => autowire(ViewComponentLoader::class)->constructorParameter(
        'viewComponentProviders',
        [
            ModuleEnum::MAINDESK->value => autowire(MaindeskProvider::class),
            ModuleEnum::COLONY->value => autowire(ColonyListProvider::class),
            ModuleEnum::SHIP->value => autowire(ShipListProvider::class),
            ModuleEnum::STATION->value => autowire(StationProvider::class),
            ModuleEnum::COMMUNICATION->value => autowire(CommunicationProvider::class),
            ModuleEnum::PM->value => autowire(MessageProvider::class),
            ModuleEnum::RESEARCH->value => autowire(ResearchProvider::class),
            ModuleEnum::TRADE->value => autowire(TradeProvider::class),
            ModuleEnum::ALLIANCE->value => autowire(AllianceProvider::class),
            ModuleEnum::DATABASE->value => autowire(DatabaseProvider::class),
            ModuleEnum::HISTORY->value => autowire(HistoryProvider::class),
            ModuleEnum::STARMAP->value => autowire(MapProvider::class),
            ModuleEnum::OPTIONS->value => autowire(PlayerSettingsProvider::class),
            ModuleEnum::USERPROFILE->value => autowire(UserProfileProvider::class),
        ]
    ),
    ShowTransfer::class => autowire(ShowTransfer::class),
    Transfer::class => autowire(Transfer::class),
    'GAME_ACTIONS' => [
        SwitchView::ACTION_IDENTIFIER => autowire(SwitchView::class),
        Logout::ACTION_IDENTIFIER => autowire(Logout::class),
        SetTutorial::ACTION_IDENTIFIER => autowire(SetTutorial::class),
        FinishTutorial::ACTION_IDENTIFIER => autowire(FinishTutorial::class)
    ],
    'GAME_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowInnerContent::VIEW_IDENTIFIER => autowire(ShowInnerContent::class),
        ShowComponent::VIEW_IDENTIFIER => autowire(ShowComponent::class),
        ShowPadd::VIEW_IDENTIFIER => autowire(ShowPadd::class),
        ShowTutorialCloseButton::VIEW_IDENTIFIER => autowire(ShowTutorialCloseButton::class),
        Noop::VIEW_IDENTIFIER => autowire(Noop::class),
    ],
    'GAME_COMPONENTS' => [
        GameComponentEnum::COLONIES->value => autowire(ColoniesComponent::class),
        GameComponentEnum::NAGUS->value => autowire(NagusComponent::class),
        GameComponentEnum::PM->value => autowire(MessageFolderComponent::class),
        GameComponentEnum::RESEARCH->value => autowire(ResearchComponent::class),
        GameComponentEnum::SERVERTIME_AND_VERSION->value => autowire(ServertimeComponent::class),
        GameComponentEnum::USER->value => autowire(UserProfileComponent::class)
    ]
];
