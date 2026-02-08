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
    public const string ACTION_IDENTIFIER = 'B_MAINTENANCE';

    /**
     * @param array<MaintenanceHandlerInterface> $handlerList
     */
    public function __construct(private MaintenanceTickRunnerFactoryInterface $maintenanceTickRunnerFactory, private array $handlerList) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]');
            return;
        }

        // load maintance tick runner without DatabaseBackup maintenance handler
        $maintenance = $this->maintenanceTickRunnerFactory->createMaintenanceTickRunner(
            array_filter(
                $this->handlerList,
                fn (MaintenanceHandlerInterface $handler): bool => !($handler instanceof DatabaseBackup)
            )
        );

        $maintenance->run(1, 1);

        $game->getInfo()->addInformation('Der Wartungs-Tick wurde durchgeführt!');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
