<?php

namespace Stu\Module\Tick\Maintenance;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Throwable;

/**
 * Executes maintenance tasks like db backup and such
 */
final class MaintenanceTickRunner implements TickRunnerInterface
{
    private GameConfigRepositoryInterface $gameConfigRepository;

    private EntityManagerInterface $entityManager;

    private FailureEmailSenderInterface $failureEmailSender;

    /** @var array<MaintenanceHandlerInterface> */
    private array $handlerList;

    /**
     * @param array<MaintenanceHandlerInterface> $handlerList
     */
    public function __construct(
        GameConfigRepositoryInterface $gameConfigRepository,
        EntityManagerInterface $entityManager,
        FailureEmailSenderInterface $failureEmailSender,
        array $handlerList
    ) {
        $this->gameConfigRepository = $gameConfigRepository;
        $this->handlerList = $handlerList;
        $this->entityManager = $entityManager;
        $this->failureEmailSender = $failureEmailSender;
    }

    public function run(int $batchGroup, int $batchGroupCount): void
    {
        $this->setGameState(GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE);

        $this->entityManager->beginTransaction();

        try {
            foreach ($this->handlerList as $handler) {
                $handler->handle();
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            $this->setGameState(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE);

            $this->failureEmailSender->sendMail(
                'stu maintenancetick failure',
                sprintf(
                    "Current system time: %s\nThe maintenancetick cron caused an error:\n\n%s\n\n%s",
                    date('Y-m-d H:i:s'),
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );

            throw $e;
        }
        $this->setGameState(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE);
    }

    private function setGameState(int $stateId): void
    {
        $this->gameConfigRepository->updateGameState($stateId);
    }
}
