<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\StuTestCase;

class ShipTickRunnerTest extends StuTestCase
{
    /** @var MockInterface&LoggerUtilFactoryInterface */
    private MockInterface $loggerUtilFactory;

    /** @var MockInterface&EntityManagerInterface */
    private MockInterface $entityManager;

    /** @var MockInterface&FailureEmailSenderInterface */
    private MockInterface $failureEmailSender;

    /** @var MockInterface&ShipTickManagerInterface */
    private MockInterface $shipTickManager;

    private ShipTickRunner $subject;

    protected function setUp(): void
    {
        $this->loggerUtilFactory = $this->mock(LoggerUtilFactoryInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->failureEmailSender = $this->mock(FailureEmailSenderInterface::class);
        $this->shipTickManager = $this->mock(ShipTickManagerInterface::class);

        $this->subject = new ShipTickRunner(
            $this->loggerUtilFactory,
            $this->entityManager,
            $this->failureEmailSender,
            $this->shipTickManager
        );
    }

    public function testRunRuns5TimesUntilGivingUp(): void
    {
        $logger = $this->mock(LoggerUtilInterface::class);

        $errorText = 'some-error';
        $error = new Exception($errorText);

        static::expectException(Exception::class);
        static::expectExceptionMessage($errorText);

        $this->loggerUtilFactory->shouldReceive('getLoggerUtil')
            ->withNoArgs()
            ->once()
            ->andReturn($logger);

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

        $logger->shouldReceive('init')
            ->with('mail', LoggerEnum::LEVEL_ERROR)
            ->once();
        $logger->shouldReceive('log')
            ->with('  rollback')
            ->times(5);

        for ($i = 4; $i >= 0; $i--) {
            $logger->shouldReceive('log')
                ->with(
                    sprintf(
                        "Shiptick caused an exception. Remaing tries: %d\nException-Message: %s\nException-Trace: %s",
                        $i,
                        $errorText,
                        $error->getTraceAsString()
                    )
                )
                ->times(1);
        }

        $this->failureEmailSender->shouldReceive('sendMail')
            ->with(
                'stu shiptick failure',
                sprintf(
                    "Current system time: %s\nThe shiptick cron caused an error:\n\n%s\n\n%s",
                    date('Y-m-d H:i:s'),
                    $errorText,
                    $error->getTraceAsString()
                )
            )
            ->once();

        $this->subject->run();
    }

    public function testRunRunsShipTick(): void
    {
        $logger = $this->mock(LoggerUtilInterface::class);

        $logger->shouldReceive('init')
            ->with('mail', LoggerEnum::LEVEL_ERROR)
            ->once();

        $this->loggerUtilFactory->shouldReceive('getLoggerUtil')
            ->withNoArgs()
            ->once()
            ->andReturn($logger);

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

        $this->subject->run();
    }
}
