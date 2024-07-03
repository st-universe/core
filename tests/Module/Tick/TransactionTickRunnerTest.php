<?php

declare(strict_types=1);

namespace Stu\Module\Tick;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Mockery\MockInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\StuTestCase;

class TransactionTickRunnerTest extends StuTestCase
{
    /** @var MockInterface&GameControllerInterface */
    private MockInterface $game;

    /** @var MockInterface&EntityManagerInterface */
    private MockInterface $entityManager;

    /** @var MockInterface&FailureEmailSenderInterface */
    private MockInterface $failureEmailSender;

    private TransactionTickRunnerInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->game = $this->mock(GameControllerInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->failureEmailSender = $this->mock(FailureEmailSenderInterface::class);

        $this->subject = new TransactionTickRunner(
            $this->game,
            $this->entityManager,
            $this->failureEmailSender
        );
    }

    public function testRunWithResetCheckExpectNothingWhenGameInResetState(): void
    {
        $this->game->shouldReceive('getGameState')
            ->withNoArgs()
            ->once()
            ->andReturn(GameEnum::CONFIG_GAMESTATE_VALUE_RESET);

        $this->subject->runWithResetCheck(fn (): bool => true, "", 1, 2);
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
            ->andReturn(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE);

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
            ->andReturn(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE);

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
