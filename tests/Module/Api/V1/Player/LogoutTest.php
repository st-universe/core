<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player;

use Mockery\MockInterface;
use Stu\Lib\SessionInterface as InternalSessionInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuApiV1TestCase;

class LogoutTest extends StuApiV1TestCase
{

    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    /**
     * @var null|MockInterface|InternalSessionInterface
     */
    private $internalSession;

    public function setUp(): void
    {
        $this->session = $this->mock(SessionInterface::class);
        $this->internalSession = $this->mock(InternalSessionInterface::class);

        parent::setUpApiHandler(
            new Logout(
                $this->session,
                $this->internalSession
            )
        );
    }

    public function testLogoutLogsOut(): void
    {
        $user = $this->mock(UserInterface::class);

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->internalSession->shouldReceive('logout')
            ->with($user)
            ->once();

        $this->response->shouldReceive('withData')
            ->with(true)
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
