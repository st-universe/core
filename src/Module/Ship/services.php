<?php

declare(strict_types=1);

namespace Stu\Module\Ship;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
use Stu\Module\Ship\Action\CreateFleet\CreateFleet;
use Stu\Module\Ship\Action\CreateFleet\CreateFleetRequest;
use Stu\Module\Ship\Action\CreateFleet\CreateFleetRequestInterface;
use Stu\Module\Ship\Action\DeleteFleet\DeleteFleet;
use Stu\Module\Ship\Action\DeleteFleet\DeleteFleetRequest;
use Stu\Module\Ship\Action\DeleteFleet\DeleteFleetRequestInterface;
use Stu\Module\Ship\Action\DisplayNotOwner\DisplayNotOwner;
use Stu\Module\Ship\Action\JoinFleet\JoinFleet;
use Stu\Module\Ship\Action\JoinFleet\JoinFleetRequest;
use Stu\Module\Ship\Action\JoinFleet\JoinFleetRequestInterface;
use Stu\Module\Ship\Action\LeaveFleet\LeaveFleet;
use Stu\Module\Ship\Action\LeaveFleet\LeaveFleetRequest;
use Stu\Module\Ship\Action\LeaveFleet\LeaveFleetRequestInterface;
use Stu\Module\Ship\Action\RenameFleet\RenameFleet;
use Stu\Module\Ship\Action\RenameFleet\RenameFleetRequest;
use Stu\Module\Ship\Action\RenameFleet\RenameFleetRequestInterface;
use Stu\Module\Ship\Action\SelfDestructConfirmation\SelfDestructConfirmation;
use Stu\Module\Ship\View\Overview\Overview;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    CreateFleetRequestInterface::class => autowire(CreateFleetRequest::class),
    DeleteFleetRequestInterface::class => autowire(DeleteFleetRequest::class),
    RenameFleetRequestInterface::class => autowire(RenameFleetRequest::class),
    LeaveFleetRequestInterface::class => autowire(LeaveFleetRequest::class),
    JoinFleetRequestInterface::class => autowire(JoinFleetRequest::class),
    IntermediateController::TYPE_SHIP_LIST => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
                DisplayNotOwner::ACTION_IDENTIFIER => autowire(DisplayNotOwner::class),
                SelfDestructConfirmation::ACTION_IDENTIFIER => autowire(SelfDestructConfirmation::class),
                CreateFleet::ACTION_IDENTIFIER => autowire(CreateFleet::class),
                DeleteFleet::ACTION_IDENTIFIER => autowire(DeleteFleet::class),
                RenameFleet::ACTION_IDENTIFIER => autowire(RenameFleet::class),
                LeaveFleet::ACTION_IDENTIFIER => autowire(LeaveFleet::class),
                JoinFleet::ACTION_IDENTIFIER => autowire(JoinFleet::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
            ]
        ),
];