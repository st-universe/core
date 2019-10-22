<?php

declare(strict_types=1);

namespace Stu\Component\Player\Invitation;

use DateTime;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitationInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;

class InvitePlayerTest extends MockeryTestCase
{

    /**
     * @var null|MockInterface|UserInvitationRepositoryInterface
     */
    private $userInvitationRepository;

    /**
     * @var null|InvitePlayer
     */
    private $handler;

    public function setUp(): void
    {
        $this->userInvitationRepository = Mockery::mock(UserInvitationRepositoryInterface::class);

        $this->handler = new InvitePlayer(
            $this->userInvitationRepository
        );
    }

    public function testInviteCreatesInvitation(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $invitation = Mockery::mock(UserInvitationInterface::class);

        $this->userInvitationRepository->shouldReceive('prototype->setUser')
            ->with($user)
            ->once()
            ->andReturn($invitation);

        $invitation->shouldReceive('setDate')
            ->with(Mockery::type(DateTime::class))
            ->once()
            ->andReturnSelf();
        $invitation->shouldReceive('setToken')
            ->with(Mockery::type('string'))
            ->once()
            ->andReturnSelf();

        $this->userInvitationRepository->shouldReceive('save')
            ->with($invitation)
            ->once();

        $this->assertSame(
            $invitation,
            $this->handler->invite($user)
        );
    }
}
