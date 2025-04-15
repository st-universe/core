<?php

declare(strict_types=1);

namespace Stu\Html\Spacecraft;

use Stu\Component\Game\ModuleEnum;
use Stu\Config\Init;
use Stu\Module\Control\ViewControllerInterface;
use Stu\TwigTestCase;

class ShowMessengerStyleInboxTest extends TwigTestCase
{
    public function testHandle(): void
    {
        $this->renderSnapshot(
            102,
            Init::getContainer()
                ->getDefinedImplementationsOf(ViewControllerInterface::class, true)->get('PM_VIEWS-DEFAULT_VIEW'),
            [
                'view' => ModuleEnum::PM->value,
                'switch' => 1
            ]
        );
    }
}
