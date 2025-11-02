<?php

declare(strict_types=1);

namespace Stu\Module\NPC;

use Stu\Module\Control\GameController;
use Stu\Module\NPC\Action\CommodityCheat;
use Stu\Module\NPC\Action\PrestigeCheat;
use Stu\Module\NPC\View\NPCLog\NPCLog;
use Stu\Module\NPC\View\Overview\Overview;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Module\NPC\View\ShowNPCSettings\ShowNPCSettings;
use Stu\Module\NPC\Action\CreateBuildplan;
use Stu\Module\NPC\Action\CreateHistoryEntry;
use Stu\Module\NPC\View\ShowBuildplanCreator\ShowBuildplanCreator;
use Stu\Module\NPC\Action\CreateShip;
use Stu\Module\NPC\Action\DeleteBuildplan;
use Stu\Module\NPC\Action\DeleteSpacecraft;
use Stu\Module\NPC\View\ShowShipCreator\ShowShipCreator;
use Stu\Module\NPC\View\ShowPlayerList\ShowPlayerList;
use Stu\Module\NPC\View\ShowMemberRumpInfo\ShowMemberRumpInfo;
use Stu\Module\NPC\View\ShowPlayerDetails\ShowPlayerDetails;
use Stu\Module\NPC\View\ShowNPCQuests\ShowNPCQuests;
use Stu\Module\NPC\Action\RenameBuildplan;
use Stu\Module\NPC\Action\SaveWelcomeMessage;
use Stu\Module\NPC\Action\LogPlayerDetails;
use Stu\Module\NPC\Action\CreateNPCQuest\CreateNPCQuest;
use Stu\Module\NPC\Action\AcceptQuestApplication\AcceptQuestApplication;
use Stu\Module\NPC\Action\RejectQuestApplication\RejectQuestApplication;
use Stu\Module\NPC\Action\InviteQuestUsers\InviteQuestUsers;
use Stu\Module\NPC\Action\ExcludeQuestUsers\ExcludeQuestUsers;

use function DI\autowire;

return [
    'NPC_ACTIONS' => [
        CommodityCheat::ACTION_IDENTIFIER => autowire(CommodityCheat::class),
        PrestigeCheat::ACTION_IDENTIFIER => autowire(PrestigeCheat::class),
        CreateBuildplan::ACTION_IDENTIFIER => autowire(CreateBuildplan::class),
        CreateHistoryEntry::ACTION_IDENTIFIER => autowire(CreateHistoryEntry::class),
        CreateShip::ACTION_IDENTIFIER => autowire(CreateShip::class),
        DeleteBuildplan::ACTION_IDENTIFIER => autowire(DeleteBuildplan::class),
        RenameBuildplan::ACTION_IDENTIFIER => autowire(RenameBuildplan::class),
        DeleteSpacecraft::ACTION_IDENTIFIER => autowire(DeleteSpacecraft::class),
        SaveWelcomeMessage::ACTION_IDENTIFIER => autowire(SaveWelcomeMessage::class),
        LogPlayerDetails::ACTION_IDENTIFIER => autowire(LogPlayerDetails::class),
        CreateNPCQuest::ACTION_IDENTIFIER => autowire(CreateNPCQuest::class),
        AcceptQuestApplication::ACTION_IDENTIFIER => autowire(AcceptQuestApplication::class),
        RejectQuestApplication::ACTION_IDENTIFIER => autowire(RejectQuestApplication::class),
        InviteQuestUsers::ACTION_IDENTIFIER => autowire(InviteQuestUsers::class),
        ExcludeQuestUsers::ACTION_IDENTIFIER => autowire(ExcludeQuestUsers::class)
    ],
    'NPC_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        NPCLog::VIEW_IDENTIFIER => autowire(NPCLog::class),
        ShowTools::VIEW_IDENTIFIER => autowire(ShowTools::class),
        ShowBuildplanCreator::VIEW_IDENTIFIER => autowire(ShowBuildplanCreator::class),
        ShowShipCreator::VIEW_IDENTIFIER => autowire(ShowShipCreator::class),
        ShowNPCSettings::VIEW_IDENTIFIER => autowire(ShowNPCSettings::class),
        ShowPlayerList::VIEW_IDENTIFIER => autowire(ShowPlayerList::class),
        ShowPlayerDetails::VIEW_IDENTIFIER => autowire(ShowPlayerDetails::class),
        ShowMemberRumpInfo::VIEW_IDENTIFIER => autowire(ShowMemberRumpInfo::class),
        ShowNPCQuests::VIEW_IDENTIFIER => autowire(ShowNPCQuests::class)
    ]
];
