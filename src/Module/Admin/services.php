<?php

declare(strict_types=1);

namespace Stu\Module\Admin;

use Stu\Module\Admin\Action\BlockUser;
use Stu\Module\Admin\Action\ClearFaultyBBCodes;
use Stu\Module\Admin\Action\CreateMissingUserWards;
use Stu\Module\Admin\Action\LockUser;
use Stu\Module\Admin\Action\Map\CreateInfluenceAreas\CreateInfluenceAreas;
use Stu\Module\Admin\Action\Map\EditField\EditField;
use Stu\Module\Admin\Action\Map\EditField\EditFieldRequest;
use Stu\Module\Admin\Action\Map\EditField\EditFieldRequestInterface;
use Stu\Module\Admin\Action\Map\EditSystemType\EditSystemType;
use Stu\Module\Admin\Action\Map\EditSystemType\EditSystemTypeRequest;
use Stu\Module\Admin\Action\Map\EditSystemType\EditSystemTypeRequestInterface;
use Stu\Module\Admin\Action\Map\EditSystemField\EditSystemField;
use Stu\Module\Admin\Action\Map\EditSystemField\EditSystemFieldRequest;
use Stu\Module\Admin\Action\Map\EditSystemField\EditSystemFieldRequestInterface;
use Stu\Module\Admin\Action\Map\EditRegion\EditRegion;
use Stu\Module\Admin\Action\Map\EditRegion\EditRegionRequest;
use Stu\Module\Admin\Action\Map\EditRegion\EditRegionRequestInterface;
use Stu\Module\Admin\Action\Map\EditAdminRegion\EditAdminRegion;
use Stu\Module\Admin\Action\Map\EditAdminRegion\EditAdminRegionRequest;
use Stu\Module\Admin\Action\Map\EditAdminRegion\EditAdminRegionRequestInterface;
use Stu\Module\Admin\Action\Map\EditPassable\EditPassable;
use Stu\Module\Admin\Action\Map\EditPassable\EditPassableRequest;
use Stu\Module\Admin\Action\Map\EditPassable\EditPassableRequestInterface;
use Stu\Module\Admin\Action\ResetCaches;
use Stu\Module\Admin\Action\SendMassMail;
use Stu\Module\Admin\Action\StartMirrorWorld;
use Stu\Module\Admin\Action\Ticks\Colony\ManualColonyTick;
use Stu\Module\Admin\Action\Ticks\Colony\ManualColonyTickRequest;
use Stu\Module\Admin\Action\Ticks\Colony\ManualColonyTickRequestInterface;
use Stu\Module\Admin\Action\Ticks\DoColonyCorrection;
use Stu\Module\Admin\Action\Ticks\DoManualMaintenance;
use Stu\Module\Admin\Action\Ticks\DoManualProcessTick;
use Stu\Module\Admin\Action\Ticks\DoManualShipTick;
use Stu\Module\Admin\Action\UnlockUser;
use Stu\Module\Admin\View\Map\EditSection\EditSection;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Admin\View\Map\ShowMapEditor;
use Stu\Module\Admin\View\Map\ShowMapInfluenceAreas;
use Stu\Module\Admin\View\Map\ShowMapOverall;
use Stu\Module\Admin\View\Map\ShowSystem\ShowSystem;
use Stu\Module\Admin\View\Map\ShowSystem\ShowSystemRequest;
use Stu\Module\Admin\View\Map\ShowSystem\ShowSystemRequestInterface;
use Stu\Module\Admin\View\Map\ShowSystemEditField\ShowSystemEditField;
use Stu\Module\Admin\View\Map\ShowSystemEditField\ShowSystemEditFieldRequest;
use Stu\Module\Admin\View\Map\ShowSystemEditField\ShowSystemEditFieldRequestInterface;
use Stu\Module\Admin\View\MassMail\MassMail;
use Stu\Module\Admin\View\Overview\Overview;
use Stu\Module\Admin\View\Playerlist\Playerlist;
use Stu\Module\Admin\View\ResearchTree\ShowResearchTree;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Admin\View\ShowSignatures\ShowSignatures;
use Stu\Module\Admin\View\ShowUserLock\ShowUserLock;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Module\Control\GameController;

use function DI\autowire;
use function DI\get;

return [
    EditFieldRequestInterface::class => autowire(EditFieldRequest::class),
    EditSystemTypeRequestInterface::class => autowire(EditSystemTypeRequest::class),
    ShowSystemRequestInterface::class => autowire(ShowSystemRequest::class),
    ShowSystemEditFieldRequestInterface::class => autowire(ShowSystemEditFieldRequest::class),
    EditSystemFieldRequestInterface::class => autowire(EditSystemFieldRequest::class),
    EditRegionRequestInterface::class => autowire(EditRegionRequest::class),
    EditAdminRegionRequestInterface::class => autowire(EditAdminRegionRequest::class),
    EditPassableRequestInterface::class => autowire(EditPassableRequest::class),
    ManualColonyTickRequestInterface::class => autowire(ManualColonyTickRequest::class),
    'ADMIN_ACTIONS' => [
        EditField::ACTION_IDENTIFIER => autowire(EditField::class),
        EditSystemType::ACTION_IDENTIFIER => autowire(EditSystemType::class),
        EditSystemField::ACTION_IDENTIFIER => autowire(EditSystemField::class),
        EditRegion::ACTION_IDENTIFIER => autowire(EditRegion::class),
        EditAdminRegion::ACTION_IDENTIFIER => autowire(EditAdminRegion::class),
        EditPassable::ACTION_IDENTIFIER => autowire(EditPassable::class),
        DoColonyCorrection::ACTION_IDENTIFIER => autowire(DoColonyCorrection::class),
        ManualColonyTick::ACTION_IDENTIFIER => autowire(ManualColonyTick::class),
        DoManualMaintenance::ACTION_IDENTIFIER => autowire(DoManualMaintenance::class)
            ->constructorParameter(
                'handlerList',
                get('maintenance_handler')
            ),
        DoManualShipTick::ACTION_IDENTIFIER => autowire(DoManualShipTick::class),
        DoManualProcessTick::ACTION_IDENTIFIER => autowire(DoManualProcessTick::class)
            ->constructorParameter(
                'tickHandler',
                get('process_tick_handler')
            ),
        CreateInfluenceAreas::ACTION_IDENTIFIER => autowire(CreateInfluenceAreas::class),
        ClearFaultyBBCodes::ACTION_IDENTIFIER => autowire(ClearFaultyBBCodes::class),
        SendMassMail::ACTION_IDENTIFIER => autowire(SendMassMail::class),
        LockUser::ACTION_IDENTIFIER => autowire(LockUser::class),
        UnlockUser::ACTION_IDENTIFIER => autowire(UnlockUser::class),
        BlockUser::ACTION_IDENTIFIER => autowire(BlockUser::class),
        StartMirrorWorld::ACTION_IDENTIFIER => autowire(StartMirrorWorld::class),
        ResetCaches::ACTION_IDENTIFIER => autowire(ResetCaches::class),
        CreateMissingUserWards::ACTION_IDENTIFIER => autowire(CreateMissingUserWards::class)
    ],
    'ADMIN_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        Playerlist::VIEW_IDENTIFIER => autowire(Playerlist::class),
        ShowMapEditor::VIEW_IDENTIFIER => autowire(ShowMapEditor::class),
        ShowMapOverall::VIEW_IDENTIFIER => autowire(ShowMapOverall::class),
        ShowScripts::VIEW_IDENTIFIER => autowire(ShowScripts::class),
        ShowTicks::VIEW_IDENTIFIER => autowire(ShowTicks::class),
        EditSection::VIEW_IDENTIFIER => autowire(EditSection::class),
        ShowSystem::VIEW_IDENTIFIER => autowire(ShowSystem::class),
        ShowSystemEditField::VIEW_IDENTIFIER => autowire(ShowSystemEditField::class),
        Noop::VIEW_IDENTIFIER => autowire(Noop::class),
        ShowMapInfluenceAreas::VIEW_IDENTIFIER => autowire(ShowMapInfluenceAreas::class),
        ShowSignatures::VIEW_IDENTIFIER => autowire(ShowSignatures::class),
        ShowResearchTree::VIEW_IDENTIFIER => autowire(ShowResearchTree::class),
        MassMail::VIEW_IDENTIFIER => autowire(MassMail::class),
        ShowUserLock::VIEW_IDENTIFIER => autowire(ShowUserLock::class)
    ]
];
