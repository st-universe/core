<?php

declare(strict_types=1);

namespace Stu\Module\Starmap;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\Starmap\Action\EditField\EditField;
use Stu\Module\Starmap\Action\EditField\EditFieldRequest;
use Stu\Module\Starmap\Action\EditField\EditFieldRequestInterface;
use Stu\Module\Starmap\Action\EditSystemField\EditSystemField;
use Stu\Module\Starmap\Action\EditSystemField\EditSystemFieldRequest;
use Stu\Module\Starmap\Action\EditSystemField\EditSystemFieldRequestInterface;
use Stu\Module\Starmap\View\EditSection\EditSection;
use Stu\Module\Starmap\View\EditSection\EditSectionRequest;
use Stu\Module\Starmap\View\EditSection\EditSectionRequestInterface;
use Stu\Module\Starmap\View\Noop\Noop;
use Stu\Module\Starmap\View\Overview\Overview;
use Stu\Module\Starmap\View\ShowByPosition\ShowByPosition;
use Stu\Module\Starmap\View\ShowByPosition\ShowByPositionRequest;
use Stu\Module\Starmap\View\ShowByPosition\ShowByPositionRequestInterface;
use Stu\Module\Starmap\View\ShowEditor\ShowEditor;
use Stu\Module\Starmap\View\ShowOverall\ShowOverall;
use Stu\Module\Starmap\View\ShowSection\ShowSection;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequest;
use Stu\Module\Starmap\View\ShowSection\ShowSectionRequestInterface;
use Stu\Module\Starmap\View\ShowSystem\ShowSystem;
use Stu\Module\Starmap\View\ShowSystem\ShowSystemRequest;
use Stu\Module\Starmap\View\ShowSystem\ShowSystemRequestInterface;
use Stu\Module\Starmap\View\ShowSystemEditField\ShowSystemEditField;
use Stu\Module\Starmap\View\ShowSystemEditField\ShowSystemEditFieldRequest;
use Stu\Module\Starmap\View\ShowSystemEditField\ShowSystemEditFieldRequestInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    ShowSectionRequestInterface::class => autowire(ShowSectionRequest::class),
    ShowByPositionRequestInterface::class => autowire(ShowByPositionRequest::class),
    EditSectionRequestInterface::class => autowire(EditSectionRequest::class),
    EditFieldRequestInterface::class => autowire(EditFieldRequest::class),
    ShowSystemRequestInterface::class => autowire(ShowSystemRequest::class),
    ShowSystemEditFieldRequestInterface::class => autowire(ShowSystemEditFieldRequest::class),
    EditSystemFieldRequestInterface::class => autowire(EditSystemFieldRequest::class),
    IntermediateController::TYPE_STARMAP => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
                EditField::ACTION_IDENTIFIER => autowire(EditField::class),
                EditSystemField::ACTION_IDENTIFIER => autowire(EditSystemField::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
                ShowSection::VIEW_IDENTIFIER => autowire(ShowSection::class),
                ShowByPosition::VIEW_IDENTIFIER => autowire(ShowByPosition::class),
                EditSection::VIEW_IDENTIFIER => autowire(EditSection::class),
                ShowEditor::VIEW_IDENTIFIER => autowire(ShowEditor::class),
                ShowOverall::VIEW_IDENTIFIER => autowire(ShowOverall::class),
                Noop::VIEW_IDENTIFIER => autowire(Noop::class),
                ShowSystem::VIEW_IDENTIFIER => autowire(ShowSystem::class),
                ShowSystemEditField::VIEW_IDENTIFIER => autowire(ShowSystemEditField::class),
            ]
        ),
];