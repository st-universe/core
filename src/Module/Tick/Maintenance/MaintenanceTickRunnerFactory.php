<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Maintenance;

use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

/**
 * Creates the MaintenanceTickRunner with a defined handler list
 */
final class MaintenanceTickRunnerFactory implements MaintenanceTickRunnerFactoryInterface
{
    private GameConfigRepositoryInterface $gameConfigRepository;

    private TransactionTickRunnerInterface $transactionTickRunner;

    public function __construct(
        GameConfigRepositoryInterface $gameConfigRepository,
        TransactionTickRunnerInterface $transactionTickRunner
    ) {
        $this->gameConfigRepository = $gameConfigRepository;
        $this->transactionTickRunner = $transactionTickRunner;
    }

    /**
     * @param array<MaintenanceHandlerInterface> $handlerList
     */
    public function createMaintenanceTickRunner(
        array $handlerList
    ): TickRunnerInterface {
        return new MaintenanceTickRunner(
            $this->gameConfigRepository,
            $this->transactionTickRunner,
            $handlerList
        );
    }
}
