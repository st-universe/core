<?php

declare(strict_types=1);

namespace Stu;

use Override;
use Mockery\MockInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\StuTestCase;

abstract class ActionControllerTestCase extends StuTestCase
{
    /** @var MockInterface&GameControllerInterface */
    protected MockInterface $game;

    #[Override]
    protected function setUp(): void
    {
        $this->game = $this->mock(GameControllerInterface::class);
    }
}
