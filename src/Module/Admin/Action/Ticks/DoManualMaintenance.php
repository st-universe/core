<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Maintenance\DatabaseBackup;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\Maintenance\MaintenanceTickRunnerFactoryInterface;

final class DoManualMaintenance implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MAINTENANCE';

    private MaintenanceTickRunnerFactoryInterface $maintenanceTickRunnerFactory;

    /** @var array<MaintenanceHandlerInterface> */
    private array $handlerList;

    /**
     * @param array<MaintenanceHandlerInterface> $handlerList
     */
    public function __construct(
        MaintenanceTickRunnerFactoryInterface $maintenanceTickRunnerFactory,
        array $handlerList
    ) {
        $this->maintenanceTickRunnerFactory = $maintenanceTickRunnerFactory;
        $this->handlerList = $handlerList;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]');
            return;
        }

        // load maintance tick runner without DatabaseBackup maintenance handler
        $maintenance = $this->maintenanceTickRunnerFactory->createMaintenanceTickRunner(
            array_filter(
                $this->handlerList,
                fn (MaintenanceHandlerInterface $handler): bool => !($handler instanceof DatabaseBackup)
            )
        );

        $maintenance->run();

        $game->addInformation('Der Wartungs-Tick wurde durchgeführt!');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
