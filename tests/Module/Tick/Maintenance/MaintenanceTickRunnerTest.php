<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Maintenance;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\StuTestCase;

class MaintenanceTickRunnerTest extends StuTestCase
{
    /** @var MockInterface&GameConfigRepositoryInterface */
    private MockInterface $gameConfigRepository;

    /** @var MockInterface&EntityManagerInterface */
    private MockInterface $entityManager;

    /** @var MockInterface&FailureEmailSenderInterface */
    private MockInterface $failureEmailSender;

    /** @var MockInterface&MaintenanceHandlerInterface */
    private MockInterface $maintenanceHandler;

    private MaintenanceTickRunner $subject;

    protected function setUp(): void
    {
        $this->gameConfigRepository = $this->mock(GameConfigRepositoryInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->failureEmailSender = $this->mock(FailureEmailSenderInterface::class);

        $this->maintenanceHandler = $this->mock(MaintenanceHandlerInterface::class);

        $this->subject = new MaintenanceTickRunner(
            $this->gameConfigRepository,
            $this->entityManager,
            $this->failureEmailSender,
            [
                $this->maintenanceHandler,
            ]
        );
    }

    public function testRunErrorsOnInternalError(): void
    {
        $errorMessage = 'some-error';
        $error = new Exception($errorMessage);

        static::expectException(Exception::class);
        static::expectExceptionMessage($errorMessage);

        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE)
            ->once();
        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE)
            ->once();

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('rollback')
            ->withNoArgs()
            ->once();

        $this->failureEmailSender->shouldReceive('sendMail')
            ->with(
                'stu maintenancetick failure',
                Mockery::type('string')
            )
            ->once();

        $this->maintenanceHandler->shouldReceive('handle')
            ->withNoArgs()
            ->once()
            ->andThrow($error);

        $this->subject->run();
    }

    public function testRunRuns(): void
    {
        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE)
            ->once();
        $this->gameConfigRepository->shouldReceive('updateGameState')
            ->with(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE)
            ->once();

        $this->entityManager->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();
        $this->entityManager->shouldReceive('commit')
            ->withNoArgs()
            ->once();

        $this->maintenanceHandler->shouldReceive('handle')
            ->withNoArgs()
            ->once();

        $this->subject->run();
    }
}
