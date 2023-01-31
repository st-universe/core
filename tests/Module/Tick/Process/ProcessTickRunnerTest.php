<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\StuTestCase;

class ProcessTickRunnerTest extends StuTestCase
{
    /** @var MockInterface&EntityManagerInterface */
    private MockInterface $entityManager;

    /** @var MockInterface&FailureEmailSenderInterface */
    private MockInterface $failureEmailSender;

    /** @var MockInterface&ProcessTickHandlerInterface */
    private MockInterface $handler;

    private ProcessTickRunner $subject;

    protected function setUp(): void
    {
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->failureEmailSender = $this->mock(FailureEmailSenderInterface::class);

        $this->handler = $this->mock(ProcessTickHandlerInterface::class);

        $this->subject = new ProcessTickRunner(
            $this->entityManager,
            $this->failureEmailSender,
            [
                $this->handler
            ]
        );
    }

    public function testRunErrorsOnInternalError(): void
    {
        $errorMessage = 'some-error';
        $error = new Exception($errorMessage);

        static::expectException(Exception::class);
        static::expectExceptionMessage($errorMessage);

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('rollback')
            ->withNoArgs()
            ->once();

        $this->handler->shouldReceive('work')
            ->withNoArgs()
            ->once()
            ->andThrow($error);

        $this->failureEmailSender
            ->shouldReceive('sendMail')
            ->with(
                'stu processtick failure',
                Mockery::type('string')
            )
            ->once();

        $this->subject->run();
    }

    public function testRunRuns(): void
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

        $this->handler->shouldReceive('work')
            ->withNoArgs()
            ->once();

        $this->subject->run();
    }
}
