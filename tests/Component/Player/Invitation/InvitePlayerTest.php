<?php

declare(strict_types=1);

namespace Stu\Component\Player\Invitation;

use DateTime;
use Mockery;
use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserInvitationInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;
use Stu\StuTestCase;

class InvitePlayerTest extends StuTestCase
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
        $user = $this->mock(UserInterface::class);
        $invitation = $this->mock(UserInvitationInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(42);

        $this->userInvitationRepository->shouldReceive('prototype->setUserId')
            ->with(42)
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
