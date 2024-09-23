<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion;

use JBBCode\Parser;
use Override;
use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PlayerDeletion implements PlayerDeletionInterface
{
    //3 days
    public const int USER_IDLE_REGISTRATION = 259200;

    //3 months
    public const int USER_IDLE_TIME = 7_905_600;

    //6 months
    public const int USER_IDLE_TIME_VACATION = 15_811_200;

    private LoggerUtilInterface $loggerUtil;

    /**
     * @param array<PlayerDeletionHandlerInterface> $deletionHandler
     */
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private StuConfigInterface $config,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private Parser $bbCodeParser,
        private array $deletionHandler
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handleDeleteable(): void
    {
        $this->loggerUtil->init('DEL', LoggerEnum::LEVEL_ERROR);

        //delete all accounts that have not been activated
        $list = $this->userRepository->getIdleRegistrations(
            time() - self::USER_IDLE_REGISTRATION
        );
        foreach ($list as $player) {
            $this->delete($player);
        }

        //delete all other deleatable accounts
        $list = $this->userRepository->getDeleteable(
            time() - self::USER_IDLE_TIME,
            time() - self::USER_IDLE_TIME_VACATION,
            $this->config->getGameSettings()->getAdminIds()
        );
        foreach ($list as $player) {
            $this->delete($player);
        }
    }

    #[Override]
    public function handleReset(): void
    {
        foreach ($this->userRepository->getNonNpcList() as $player) {
            $this->delete($player);
        }
    }

    private function delete(UserInterface $user): void
    {
        $userId = $user->getId();
        $name = $this->bbCodeParser->parse($user->getName())->getAsText();
        $delmark = $user->getDeletionMark();

        $this->loggerUtil->log(sprintf('deleting userId: %d', $userId));

        array_walk(
            $this->deletionHandler,
            function (PlayerDeletionHandlerInterface $handler) use ($user): void {
                $handler->delete($user);
            }
        );

        $this->loggerUtil->log(sprintf('deleted user (id: %d, name: %s, delmark: %d)', $userId, $name, $delmark));
    }
}
