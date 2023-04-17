<?php

namespace Stu\Module\Tick\Maintenance;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Module\Tick\AbstractTickRunner;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

/**
 * Executes maintenance tasks like db backup and such
 */
final class MaintenanceTickRunner extends AbstractTickRunner
{
    private GameConfigRepositoryInterface $gameConfigRepository;

    /** @var array<MaintenanceHandlerInterface> */
    private array $handlerList;

    /**
     * @param array<MaintenanceHandlerInterface> $handlerList
     */
    public function __construct(
        GameControllerInterface $game,
        GameConfigRepositoryInterface $gameConfigRepository,
        EntityManagerInterface $entityManager,
        FailureEmailSenderInterface $failureEmailSender,
        array $handlerList
    ) {
        parent::__construct($game, $entityManager, $failureEmailSender);
        $this->gameConfigRepository = $gameConfigRepository;
        $this->handlerList = $handlerList;
    }

    public function runInTransaction(int $batchGroup, int $batchGroupCount): void
    {
        $this->setGameState(GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE);

        foreach ($this->handlerList as $handler) {
            $handler->handle();
        }

        $this->setGameState(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE);
    }

    private function setGameState(int $stateId): void
    {
        $this->gameConfigRepository->updateGameState($stateId);
    }

    public function getTickDescription(): string
    {
        return "maintenancetick";
    }
}
