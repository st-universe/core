<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Maintenance\DatabaseBackup;
use Stu\Module\Tick\Maintenance\MaintenanceTick;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

final class DoManualMaintenance implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MAINTENANCE';

    private GameConfigRepositoryInterface $gameConfigRepository;

    private LoggerUtilFactoryInterface $loggerUtilFactory;

    private EntityManagerInterface $entityManager;

    public function __construct(
        GameConfigRepositoryInterface $gameConfigRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->gameConfigRepository = $gameConfigRepository;
        $this->loggerUtilFactory = $loggerUtilFactory;
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        global $container;

        $maintenance = new MaintenanceTick(
            $this->gameConfigRepository,
            $this->loggerUtilFactory,
            array_filter(
                $container->get('maintenance_handler'),
                function ($key): bool {
                    return $key != DatabaseBackup::class;
                },
                ARRAY_FILTER_USE_KEY
            )
        );
        $maintenance->handle();
        $this->entityManager->flush();

        $game->addInformation("Der Wartungs-Tick wurde durchgeführt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
