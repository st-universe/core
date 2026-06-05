<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\MarkPmsRead;

use Mockery\MockInterface;
use Stu\ActionControllerTestCase;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

class MarkPmsReadTest extends ActionControllerTestCase
{
    private MockInterface&MarkPmsReadRequestInterface $markPmsReadRequest;
    private MockInterface&PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;
    private MockInterface&PrivateMessageRepositoryInterface $privateMessageRepository;

    private MarkPmsRead $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->markPmsReadRequest = $this->mock(MarkPmsReadRequestInterface::class);
        $this->privateMessageFolderRepository = $this->mock(PrivateMessageFolderRepositoryInterface::class);
        $this->privateMessageRepository = $this->mock(PrivateMessageRepositoryInterface::class);

        $this->subject = new MarkPmsRead(
            $this->markPmsReadRequest,
            $this->privateMessageFolderRepository,
            $this->privateMessageRepository
        );
    }

    public function testHandleMarksFolderMessagesAsRead(): void
    {
        $folder = $this->mock(PrivateMessageFolder::class);
        $user = $this->mock(User::class);

        $this->markPmsReadRequest->shouldReceive('getCategoryId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->privateMessageFolderRepository->shouldReceive('find')
            ->with(42)
            ->once()
            ->andReturn($folder);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo->addInformation')
            ->with('Alle Nachrichten im Ordner wurden als gelesen markiert')
            ->once();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(101);

        $folder->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(101);
        $folder->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->privateMessageRepository->shouldReceive('markAsReadByFolder')
            ->with(42)
            ->once()
            ->andReturn(3);

        $this->subject->handle($this->game);
    }

    public function testHandleDoesNothingIfFolderDoesNotExist(): void
    {
        $this->markPmsReadRequest->shouldReceive('getCategoryId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->privateMessageFolderRepository->shouldReceive('find')
            ->with(42)
            ->once()
            ->andReturnNull();

        $this->game->shouldReceive('getUser')
            ->never();
        $this->privateMessageRepository->shouldReceive('markAsReadByFolder')
            ->never();
        $this->game->shouldReceive('getInfo')
            ->never();

        $this->subject->handle($this->game);
    }

    public function testHandleDoesNothingIfFolderBelongsToAnotherUser(): void
    {
        $folder = $this->mock(PrivateMessageFolder::class);
        $user = $this->mock(User::class);

        $this->markPmsReadRequest->shouldReceive('getCategoryId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->privateMessageFolderRepository->shouldReceive('find')
            ->with(42)
            ->once()
            ->andReturn($folder);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo')
            ->never();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(101);

        $folder->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(102);

        $this->privateMessageRepository->shouldReceive('markAsReadByFolder')
            ->never();

        $this->subject->handle($this->game);
    }

    public function testPerformSessionCheckReturnsTrue(): void
    {
        static::assertTrue($this->subject->performSessionCheck());
    }
}
