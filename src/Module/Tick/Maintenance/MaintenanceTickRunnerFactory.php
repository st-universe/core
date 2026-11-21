<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Maintenance;

use Doctrine\DBAL\Connection;
use Override;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Creates the MaintenanceTickRunner with a defined handler list
 */
final class MaintenanceTickRunnerFactory implements MaintenanceTickRunnerFactoryInterface
{
    /** @param array<MaintenanceHandlerInterface> $handlerList */
    public function __construct(
        private GameConfigRepositoryInterface $gameConfigRepository,
        private TransactionTickRunnerInterface $transactionTickRunner,
        private Connection $connection,
        private array $handlerList,
        private UserRepositoryInterface $users,
        private StuConfigInterface $config
    ) {}

    /**
     * @param null|array<MaintenanceHandlerInterface> $handlerList
     */
    #[Override]
    public function createMaintenanceTickRunner(
        ?array $handlerList = null
    ): TickRunnerInterface {
        return new MaintenanceTickRunner(
            $this->gameConfigRepository,
            $this->transactionTickRunner,
            $this->connection,
            $handlerList ?? $this->handlerList,
            $this->users,
            $this->config
        );
    }
}
