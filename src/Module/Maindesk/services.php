<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\Maindesk\Action\ColonizationShip\ColonizationShip;
use Stu\Module\Maindesk\Action\FirstColony\FirstColony;
use Stu\Module\Maindesk\Action\FirstColony\FirstColonyRequest;
use Stu\Module\Maindesk\Action\FirstColony\FirstColonyRequestInterface;
use Stu\Module\Maindesk\Action\SmsVerification\SmsVerification;
use Stu\Module\Maindesk\View\ShowColonyList\ShowColonyList;

use function DI\autowire;

return [
    FirstColonyRequestInterface::class => autowire(FirstColonyRequest::class),
    'MAINDESK_ACTIONS' => [
        FirstColony::ACTION_IDENTIFIER => autowire(FirstColony::class),
        ColonizationShip::ACTION_IDENTIFIER => autowire(ColonizationShip::class),
        SmsVerification::ACTION_IDENTIFIER => autowire(SmsVerification::class)
    ],
    'MAINDESK_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowColonyList::VIEW_IDENTIFIER => autowire(ShowColonyList::class)
    ],
];
