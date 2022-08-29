<?php

namespace Stu\Module\Tick\Maintenance;

use Stu\Component\Game\GameEnum;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

final class MaintenanceTick
{
    private GameConfigRepositoryInterface $gameConfigRepository;

    /**
     * @var MaintenanceHandlerInterface[]
     */
    private array $handler_list;

    public function __construct(
        GameConfigRepositoryInterface $gameConfigRepository,
        array $handler_list
    ) {
        $this->gameConfigRepository = $gameConfigRepository;
        $this->handler_list = $handler_list;
    }

    private function startMaintenance()
    {
        $option = $this->gameConfigRepository->getByOption(GameEnum::CONFIG_GAMESTATE);
        $option->setValue(GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE);

        $this->gameConfigRepository->save($option);
    }

    public function handle()
    {
        $this->startMaintenance();

        foreach ($this->handler_list as $handler) {
            $handler->handle();
        }

        $this->finishMaintenance();
    }

    private function finishMaintenance()
    {
        $option = $this->gameConfigRepository->getByOption(GameEnum::CONFIG_GAMESTATE);
        $option->setValue(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE);

        $this->gameConfigRepository->save($option);
    }
}
