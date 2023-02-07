<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\StuTestCase;

class ColonyTickRunnerTest extends StuTestCase
{
    /** @var MockInterface&EntityManagerInterface */
    private MockInterface $entityManager;

    /** @var MockInterface&ColonyTickManagerInterface */
    private MockInterface $colonyTickManager;

    /** @var MockInterface&FailureEmailSenderInterface */
    private MockInterface $failureEmailSender;

    private ColonyTickRunner $subject;

    protected function setUp(): void
    {
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->colonyTickManager = $this->mock(ColonyTickManagerInterface::class);
        $this->failureEmailSender = $this->mock(FailureEmailSenderInterface::class);

        $this->subject = new ColonyTickRunner(
            $this->entityManager,
            $this->colonyTickManager,
            $this->failureEmailSender,
            $this->initLoggerUtil()
        );
    }

    public function testRunSendFailureEmailOnError(): void
    {
        $error = 'some-error';

        static::expectException(Exception::class);
        static::expectExceptionMessage($error);

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('rollback')
            ->withNoArgs()
            ->once();

        $this->colonyTickManager->shouldReceive('work')
            ->with(1)
            ->once()
            ->andThrow(new Exception($error));

        $this->failureEmailSender->shouldReceive('sendMail')
            ->with(
                'stu colonytick failure',
                Mockery::type('string')
            )
            ->once();

        $this->subject->run();
    }

    public function testRunExecutesColonyTick(): void
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

        $this->colonyTickManager->shouldReceive('work')
            ->with(1)
            ->once();

        $this->subject->run();
    }
}
