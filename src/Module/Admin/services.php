<?php

declare(strict_types=1);

namespace Stu\Module\Admin;

use Stu\Module\Admin\Action\Map\EditField\EditField;
use Stu\Module\Admin\Action\Map\EditField\EditFieldRequest;
use Stu\Module\Admin\Action\Map\EditField\EditFieldRequestInterface;
use Stu\Module\Admin\Action\Map\EditSystemField\EditSystemField;
use Stu\Module\Admin\Action\Map\EditSystemField\EditSystemFieldRequest;
use Stu\Module\Admin\Action\Map\EditSystemField\EditSystemFieldRequestInterface;
use Stu\Module\Admin\View\Map\EditSection\EditSection;
use Stu\Module\Admin\View\Map\EditSection\EditSectionRequest;
use Stu\Module\Admin\View\Map\EditSection\EditSectionRequestInterface;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Admin\View\Map\ShowMapEditor;
use Stu\Module\Admin\View\Map\ShowMapOverall;
use Stu\Module\Admin\View\Map\ShowSystem\ShowSystem;
use Stu\Module\Admin\View\Map\ShowSystem\ShowSystemRequest;
use Stu\Module\Admin\View\Map\ShowSystem\ShowSystemRequestInterface;
use Stu\Module\Admin\View\Map\ShowSystemEditField\ShowSystemEditField;
use Stu\Module\Admin\View\Map\ShowSystemEditField\ShowSystemEditFieldRequest;
use Stu\Module\Admin\View\Map\ShowSystemEditField\ShowSystemEditFieldRequestInterface;
use Stu\Module\Admin\View\Overview\Overview;
use Stu\Module\Admin\View\Playerlist\Playerlist;
use Stu\Module\Admin\Action\Ticks\DoManualColonyTick;
use Stu\Module\Admin\Action\Ticks\DoManualShipTick;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\GameController;

use function DI\autowire;

return [
    EditSectionRequestInterface::class => autowire(EditSectionRequest::class),
    EditFieldRequestInterface::class => autowire(EditFieldRequest::class),
    ShowSystemRequestInterface::class => autowire(ShowSystemRequest::class),
    ShowSystemEditFieldRequestInterface::class => autowire(ShowSystemEditFieldRequest::class),
    EditSystemFieldRequestInterface::class => autowire(EditSystemFieldRequest::class),
    'ADMIN_ACTIONS' => [
        EditField::ACTION_IDENTIFIER => autowire(EditField::class),
        EditSystemField::ACTION_IDENTIFIER => autowire(EditSystemField::class),
        DoManualColonyTick::ACTION_IDENTIFIER => autowire(DoManualColonyTick::class),
        DoManualShipTick::ACTION_IDENTIFIER => autowire(DoManualShipTick::class)
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
    ]
];
