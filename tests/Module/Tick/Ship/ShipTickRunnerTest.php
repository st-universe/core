<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\StuTestCase;

class ShipTickRunnerTest extends StuTestCase
{
    /** @var MockInterface&ShipTickManagerInterface */
    private MockInterface $shipTickManager;

    /** @var MockInterface&TransactionTickRunnerInterface */
    private MockInterface $transactionTickRunner;

    /** @var MockInterface&FailureEmailSenderInterface */
    private MockInterface $failureEmailSender;


    /** @var MockInterface&EntityManagerInterface */
    private MockInterface $entityManager;

    private ShipTickRunner $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->shipTickManager = $this->mock(ShipTickManagerInterface::class);
        $this->transactionTickRunner = $this->mock(TransactionTickRunnerInterface::class);
        $this->failureEmailSender = $this->mock(FailureEmailSenderInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->subject = new ShipTickRunner(
            $this->shipTickManager,
            $this->transactionTickRunner,
            $this->failureEmailSender,
            $this->initLoggerUtil(),
            $this->entityManager
        );
    }

    public function testRunExpectNothingWhenGameStateReset(): void
    {
        $this->transactionTickRunner->shouldReceive('isGameStateReset')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->run(1, 1);
    }

    public function testRunRuns5TimesUntilGivingUp(): void
    {
        $errorText = 'some-error';
        $error = new Exception($errorText);

        static::expectException(Exception::class);
        static::expectExceptionMessage($errorText);

        $this->transactionTickRunner->shouldReceive('isGameStateReset')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->times(5);
        $this->entityManager->shouldReceive('rollback')
            ->withNoArgs()
            ->times(5);

        $this->shipTickManager->shouldReceive('work')
            ->withNoArgs()
            ->times(5)
            ->andThrow($error);

        $this->failureEmailSender->shouldReceive('sendMail')
            ->with(
                'stu shiptick failure',
                Mockery::type('string'),
            )
            ->once();

        $this->subject->run(1, 1);
    }

    public function testRunRunsShipTick(): void
    {
        $this->transactionTickRunner->shouldReceive('isGameStateReset')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('commit')
            ->withNoArgs()
            ->once();

        $this->shipTickManager->shouldReceive('work')
            ->withNoArgs()
            ->once();

        $this->subject->run(1, 1);
    }
}
