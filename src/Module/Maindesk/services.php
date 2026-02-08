<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Maindesk\Action\AccountVerification\AccountVerification;
use Stu\Module\Maindesk\Action\CheckInput\CheckInput;
use Stu\Module\Maindesk\Action\CheckInput\CheckInputRequest;
use Stu\Module\Maindesk\Action\CheckInput\CheckInputRequestInterface;
use Stu\Module\Maindesk\Action\ColonizationShip\ColonizationShip;
use Stu\Module\Maindesk\Action\EmailManagement\EmailManagement;
use Stu\Module\Maindesk\Action\FirstColony\FirstColony;
use Stu\Module\Maindesk\Action\FirstColony\FirstColonyRequest;
use Stu\Module\Maindesk\Action\FirstColony\FirstColonyRequestInterface;
use Stu\Module\Maindesk\Action\SmsManagement\SmsManagement;
use Stu\Module\Maindesk\View\ShowColonyList\ShowColonyList;

use function DI\autowire;

return [
    FirstColonyRequestInterface::class => autowire(FirstColonyRequest::class),
    CheckInputRequestInterface::class => autowire(CheckInputRequest::class),
    'MAINDESK_ACTIONS' => [
        CheckInput::ACTION_IDENTIFIER => autowire(CheckInput::class),
        FirstColony::ACTION_IDENTIFIER => autowire(FirstColony::class),
        ColonizationShip::ACTION_IDENTIFIER => autowire(ColonizationShip::class),
        AccountVerification::ACTION_IDENTIFIER => autowire(AccountVerification::class),
        EmailManagement::ACTION_IDENTIFIER => autowire(EmailManagement::class),
        SmsManagement::ACTION_IDENTIFIER => autowire(SmsManagement::class),
    ],
    'MAINDESK_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowColonyList::VIEW_IDENTIFIER => autowire(ShowColonyList::class)
    ],
];
