<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Mockery\MockInterface;
use Override;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\GameStateEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\StuTestCase;

class TransactionTickRunnerTest extends StuTestCase
{
    private MockInterface&FailureEmailSenderInterface $failureEmailSender;
    private MockInterface&GameControllerInterface $game;
    private MockInterface&EntityManagerInterface $entityManager;

    private TransactionTickRunnerInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->failureEmailSender = $this->mock(FailureEmailSenderInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->subject = new TransactionTickRunner(
            $this->failureEmailSender,
            $this->game,
            $this->entityManager,
            $this->initLoggerUtil()
        );
    }

    public function testRunWithResetCheckExpectNothingWhenGameInResetState(): void
    {
        $this->game->shouldReceive('getGameState')
            ->withNoArgs()
            ->once()
            ->andReturn(GameStateEnum::RESET);

        $this->subject->runWithResetCheck(fn(): bool => true, "", 1, 2);
    }

    public function testRunWithResetCheckExpectRollbackWhenExceptionOccurs(): void
    {
        static::expectExceptionMessage('foo');
        static::expectException(InvalidArgumentException::class);

        $callable = function (): void {
            throw new InvalidArgumentException('foo');
        };

        $this->game->shouldReceive('getGameState')
            ->withNoArgs()
            ->once()
            ->andReturn(GameStateEnum::ONLINE);

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('rollback')
            ->withNoArgs()
            ->once();

        $this->failureEmailSender->shouldReceive('sendMail')
            ->withAnyArgs()
            ->once();

        $this->subject->runWithResetCheck($callable, "BARTICK", 1, 2);
    }

    public function testRunWithResetCheckExpectCommitOfTransactionWhenCallableSuccessful(): void
    {
        $batchGroup = 2;
        $batchGroupCount = 5;

        $checkVar1 = 0;
        $checkVar2 = 0;

        $callable = function (int $batchGroup, int $batchGroupCount) use (&$checkVar1, &$checkVar2): void {
            $checkVar1 = $batchGroup;
            $checkVar2 = $batchGroupCount;
        };

        $this->game->shouldReceive('getGameState')
            ->withNoArgs()
            ->once()
            ->andReturn(GameStateEnum::ONLINE);

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('commit')
            ->withNoArgs()
            ->once();

        $this->subject->runWithResetCheck($callable, "", $batchGroup, $batchGroupCount);

        $this->assertEquals($batchGroup, $checkVar1);
        $this->assertEquals($batchGroupCount, $checkVar2);
    }
}
