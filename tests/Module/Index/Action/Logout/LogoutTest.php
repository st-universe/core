<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\Logout;

use Mockery\MockInterface;
use Override;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\StuTestCase;

class LogoutTest extends StuTestCase
{
    /** @var MockInterface&SessionInterface */
    private MockInterface $session;

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

        $this->subject->handle($game);
    }


    public function testHandleLogsOut(): void
    {
        $game = $this->mock(GameControllerInterface::class);

        $game->shouldReceive('hasUser')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

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
