<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Maintenance;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

/**
 * Creates the MaintenanceTickRunner with a defined handler list
 */
final class MaintenanceTickRunnerFactory implements MaintenanceTickRunnerFactoryInterface
{
    private GameConfigRepositoryInterface $gameConfigRepository;

    private EntityManagerInterface $entityManager;

    private FailureEmailSenderInterface $failureEmailSender;

    public function __construct(
        GameConfigRepositoryInterface $gameConfigRepository,
        EntityManagerInterface $entityManager,
        FailureEmailSenderInterface $failureEmailSender
    ) {
        $this->gameConfigRepository = $gameConfigRepository;
        $this->entityManager = $entityManager;
        $this->failureEmailSender = $failureEmailSender;
    }

    /**
     * @param array<MaintenanceHandlerInterface> $handlerList
     */
    public function createMaintenanceTickRunner(
        array $handlerList
    ): TickRunnerInterface {
        return new MaintenanceTickRunner(
            $this->gameConfigRepository,
            $this->entityManager,
            $this->failureEmailSender,
            $handlerList
        );
    }
}
