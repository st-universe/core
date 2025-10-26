<?php

declare(strict_types=1);

namespace Stu;

use Mockery\MockInterface;
use Stu\Module\Control\GameControllerInterface;

abstract class ActionControllerTestCase extends StuTestCase
{
    /** @var MockInterface&GameControllerInterface */
    protected MockInterface $game;

    #[\Override]
    protected function setUp(): void
    {
        $this->game = $this->mock(GameControllerInterface::class);
    }
}
