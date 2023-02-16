<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\StuTestCase;
use Ubench;

class ShipTickRunnerTest extends StuTestCase
{
    /** @var MockInterface&EntityManagerInterface */
    private MockInterface $entityManager;

    /** @var MockInterface&FailureEmailSenderInterface */
    private MockInterface $failureEmailSender;

    /** @var MockInterface&ShipTickManagerInterface */
    private MockInterface $shipTickManager;

    /** @var MockInterface&Ubench */
    private MockInterface $benchmark;

    private ShipTickRunner $subject;

    protected function setUp(): void
    {
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->failureEmailSender = $this->mock(FailureEmailSenderInterface::class);
        $this->shipTickManager = $this->mock(ShipTickManagerInterface::class);
        $this->benchmark = $this->mock(Ubench::class);

        $this->subject = new ShipTickRunner(
            $this->entityManager,
            $this->failureEmailSender,
            $this->shipTickManager,
            $this->benchmark,
            $this->initLoggerUtil()
        );
    }

    public function testRunRuns5TimesUntilGivingUp(): void
    {
        $errorText = 'some-error';
        $error = new Exception($errorText);

        static::expectException(Exception::class);
        static::expectExceptionMessage($errorText);

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

        $this->benchmark->shouldReceive('end')
            ->withNoArgs()
            ->once();
        $this->benchmark->shouldReceive('getTime')
            ->withNoArgs()
            ->once();
        $this->benchmark->shouldReceive('getMemoryUsage')
            ->withNoArgs()
            ->once();
        $this->benchmark->shouldReceive('getMemoryPeak')
            ->withNoArgs()
            ->once();

        $this->subject->run(1, 1);
    }
}
