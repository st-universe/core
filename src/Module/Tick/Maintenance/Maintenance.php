<?php

namespace Stu\Module\Tick\Maintenance;

use GameConfig;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;

final class Maintenance
{
    /**
     * @var MaintenanceHandlerInterface[]
     */
    private $handler_list;

    public function __construct(
        array $handler_list
    ) {
        $this->handler_list = $handler_list;
    }

    private function startMaintenance()
    {
        GameConfig::getObjectByOption(CONFIG_GAMESTATE)->setValue(CONFIG_GAMESTATE_VALUE_MAINTENANCE);
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
        GameConfig::getObjectByOption(CONFIG_GAMESTATE)->setValue(CONFIG_GAMESTATE_VALUE_ONLINE);
    }
}
