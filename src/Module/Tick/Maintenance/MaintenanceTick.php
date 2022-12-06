<?php

namespace Stu\Module\Tick\Maintenance;

use Stu\Component\Game\GameEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Maintenance\MaintenanceHandlerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class MaintenanceTick
{
    private GameConfigRepositoryInterface $gameConfigRepository;

    private LoggerUtilInterface $loggerUtil;
    /**
     * @var MaintenanceHandlerInterface[]
     */
    private array $handler_list;

    public function __construct(
        GameConfigRepositoryInterface $gameConfigRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        array $handler_list
    ) {
        $this->gameConfigRepository = $gameConfigRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
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

        $this->logOldPasswords();

        $this->finishMaintenance();
    }

    private function logOldPasswords(): void
    {
        $this->loggerUtil->init('PW', LoggerEnum::LEVEL_WARNING);

        global $container;

        /**
         * @var UserInterface[]
         */
        $allUsers = $container->get(UserRepositoryInterface::class)->findAll();

        foreach ($allUsers as $user) {
            $password_hash = $user->getPassword();
            $password_info = password_get_info($password_hash);

            if ($password_info['algo'] === 0) {
                $this->loggerUtil->log(sprintf('userId %d has old PW', $user->getId()));
            }
        }

        $this->loggerUtil->log('all user passwords have been checked');
    }

    private function finishMaintenance()
    {
        $option = $this->gameConfigRepository->getByOption(GameEnum::CONFIG_GAMESTATE);
        $option->setValue(GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE);

        $this->gameConfigRepository->save($option);
    }
}
