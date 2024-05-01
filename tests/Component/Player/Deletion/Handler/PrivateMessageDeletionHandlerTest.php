<?php

declare(strict_types=1);

namespace Component\Player\Deletion\Handler;

use Mockery\MockInterface;
use Stu\Component\Player\Deletion\Handler\PrivateMessageDeletionHandler;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class PrivateMessageDeletionHandlerTest extends StuTestCase
{
    /** @var UserRepositoryInterface&MockInterface */
    private MockInterface $userRepository;

    /** @var MockInterface&PrivateMessageRepositoryInterface */
    private MockInterface $privateMessageRepository;

    private PrivateMessageDeletionHandler $subject;

    protected function setUp(): void
    {
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->privateMessageRepository = $this->mock(PrivateMessageRepositoryInterface::class);

        $this->subject = new PrivateMessageDeletionHandler(
            $this->userRepository,
            $this->privateMessageRepository
        );
    }

    public function testDeleteUpdatesTheSendingUser(): void
    {
        $user = $this->mock(UserInterface::class);
        $fallbackUser = $this->mock(UserInterface::class);
        $pm = $this->mock(PrivateMessageInterface::class);

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($fallbackUser);

        $this->privateMessageRepository->shouldReceive('getBySender')
            ->with($user)
            ->once()
            ->andReturn([$pm]);
        $this->privateMessageRepository->shouldReceive('getByReceiver')
            ->with($user)
            ->once()
            ->andReturn([]);
        $this->privateMessageRepository->shouldReceive('save')
            ->with($pm)
            ->once();

        $pm->shouldReceive('setSender')
            ->with($fallbackUser)
            ->once();

        $this->subject->delete($user);
    }

    public function testDeleteDoesNothingIfOutboxNotExists(): void
    {
        $user = $this->mock(UserInterface::class);
        $fallbackUser = $this->mock(UserInterface::class);
        $pm = $this->mock(PrivateMessageInterface::class);

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($fallbackUser);

        $this->privateMessageRepository->shouldReceive('getBySender')
            ->with($user)
            ->once()
            ->andReturn([]);
        $this->privateMessageRepository->shouldReceive('getByReceiver')
            ->with($user)
            ->once()
            ->andReturn([$pm]);

        $pm->shouldReceive('getOutboxPm')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->delete($user);
    }

    public function testDeleteUnsetsInboxReferenceIfOutboxExists(): void
    {
        $user = $this->mock(UserInterface::class);
        $fallbackUser = $this->mock(UserInterface::class);
        $pm = $this->mock(PrivateMessageInterface::class);
        $outboxPm = $this->mock(PrivateMessageInterface::class);

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($fallbackUser);

        $this->privateMessageRepository->shouldReceive('getBySender')
            ->with($user)
            ->once()
            ->andReturn([]);
        $this->privateMessageRepository->shouldReceive('getByReceiver')
            ->with($user)
            ->once()
            ->andReturn([$pm]);

        $pm->shouldReceive('getOutboxPm')
            ->withNoArgs()
            ->once()
            ->andReturn($outboxPm);

        $outboxPm->shouldReceive('setInboxPm')
            ->with(null)
            ->once();

        $this->privateMessageRepository->shouldReceive('save')
            ->with($outboxPm)
            ->once();

        $this->subject->delete($user);
    }
}
