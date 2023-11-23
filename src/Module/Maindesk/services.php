<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk;

use Stu\Module\Maindesk\Action\ColonizationShip\ColonizationShip;
use Stu\Module\Maindesk\Action\FirstColony\FirstColony;
use Stu\Module\Maindesk\Action\FirstColony\FirstColonyRequest;
use Stu\Module\Maindesk\Action\FirstColony\FirstColonyRequestInterface;
use Stu\Module\Maindesk\Action\LastTutorial\LastTutorial;
use Stu\Module\Maindesk\Action\NextTutorial\NextTutorial;
use Stu\Module\Maindesk\Action\SmsVerification\SmsVerification;
use Stu\Module\Maindesk\View\ShowColonyList\ShowColonyList;

use function DI\autowire;

return [
    FirstColonyRequestInterface::class => autowire(FirstColonyRequest::class),
    'MAINDESK_ACTIONS' => [
        FirstColony::ACTION_IDENTIFIER => autowire(FirstColony::class),
        ColonizationShip::ACTION_IDENTIFIER => autowire(ColonizationShip::class),
        NextTutorial::ACTION_IDENTIFIER => autowire(NextTutorial::class),
        LastTutorial::ACTION_IDENTIFIER => autowire(LastTutorial::class),
        SmsVerification::ACTION_IDENTIFIER => autowire(SmsVerification::class)
    ],
    'MAINDESK_VIEWS' => [
        ShowColonyList::VIEW_IDENTIFIER => autowire(ShowColonyList::class)
    ],
];
