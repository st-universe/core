<?php

declare(strict_types=1);

namespace Stu;

use Mockery\MockInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;

abstract class ActionControllerTestCase extends StuTestCase
{
    /** @var MockInterface&ActionControllerContext */
    protected MockInterface $game;

    #[\Override]
    protected function setUp(): void
    {
        $this->game = $this->mock(ActionControllerContext::class);
    }
}
