<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\Logout;

use Mockery\MockInterface;
use Override;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\StuTestCase;

class LogoutTest extends StuTestCase
{
    private MockInterface&SessionInterface $session;

    private Logout $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->session = $this->mock(SessionInterface::class);

        $this->subject = new Logout(
            $this->session
        );
    }

    public function testHandleDoesNothingIfUserNotAvailable(): void
    {
        $game = $this->mock(GameControllerInterface::class);

        $game->shouldReceive('hasUser')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $game->shouldReceive('redirectTo')
            ->with('/index.php')
            ->once();

        $this->subject->handle($game);
    }


    public function testHandleLogsOut(): void
    {
        $game = $this->mock(GameControllerInterface::class);

        $game->shouldReceive('hasUser')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $game->shouldReceive('redirectTo')
            ->with('/index.php')
            ->once();

        $this->session->shouldReceive('logout')
            ->withNoArgs()
            ->once();

        $this->subject->handle($game);
    }

    public function testPerformSessionCheckReturnsFalse(): void
    {
        static::assertFalse(
            $this->subject->performSessionCheck()
        );
    }
}
