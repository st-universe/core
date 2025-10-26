<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Spacecraft;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\StuTestCase;

class SpacecraftTickRunnerTest extends StuTestCase
{
    private MockInterface&SpacecraftTickManagerInterface $spacecraftTickManager;

    private MockInterface&TransactionTickRunnerInterface $transactionTickRunner;

    private MockInterface&FailureEmailSenderInterface $failureEmailSender;


    private MockInterface&EntityManagerInterface $entityManager;

    private SpacecraftTickRunner $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraftTickManager = $this->mock(SpacecraftTickManagerInterface::class);
        $this->transactionTickRunner = $this->mock(TransactionTickRunnerInterface::class);
        $this->failureEmailSender = $this->mock(FailureEmailSenderInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->subject = new SpacecraftTickRunner(
            $this->spacecraftTickManager,
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

        $this->spacecraftTickManager->shouldReceive('work')
            ->with(true)
            ->times(5)
            ->andThrow($error);

        $this->failureEmailSender->shouldReceive('sendMail')
            ->with(
                'stu spacecrafttick failure',
                Mockery::type('string'),
            )
            ->once();

        $this->subject->run(1, 1);
    }

    public function testRunRunsSpacecraftTick(): void
    {
        $this->transactionTickRunner->shouldReceive('isGameStateReset')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();

        $this->spacecraftTickManager->shouldReceive('work')
            ->with(true)
            ->once();

        $this->subject->run(1, 1);
    }
}
