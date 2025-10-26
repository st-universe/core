<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion;

use JBBCode\Parser;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Player\Deletion\Handler\PlayerDeletionHandlerInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Logging\LogLevelEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageSender;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PlayerDeletion implements PlayerDeletionInterface
{
    //3 days
    public const int USER_IDLE_REGISTRATION = 3 * TimeConstants::ONE_DAY_IN_SECONDS;

    //3 months
    public const int USER_IDLE_TIME = 7_905_600;

    //6 months
    public const int USER_IDLE_TIME_VACATION = 15_811_200;

    private LoggerUtilInterface $loggerUtil;

    /**
     * @param array<int, PlayerDeletionHandlerInterface> $deletionHandlers
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly StuConfigInterface $config,
        private readonly Parser $bbCodeParser,
        private readonly StuTime $stuTime,
        private array $deletionHandlers,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[\Override]
    public function handleDeleteable(): void
    {
        $this->loggerUtil->init('DEL', LogLevelEnum::ERROR);

        $time = $this->stuTime->time();

        //all accounts that have not been activated
        $idleList = $this->userRepository->getIdleRegistrations(
            $time - self::USER_IDLE_REGISTRATION
        );

        //all other deleatable accounts
        $deleatableList = $this->userRepository->getDeleteable(
            $time - self::USER_IDLE_TIME,
            $time - self::USER_IDLE_TIME_VACATION,
            $this->config->getGameSettings()->getAdminIds()
        );

        $combinedList = $idleList + $deleatableList;
        PrivateMessageSender::$blockedUserIds = array_keys($combinedList);

        foreach ($combinedList as $player) {
            $this->delete($player);
        }
    }

    #[\Override]
    public function handleReset(): void
    {
        foreach ($this->userRepository->getNonNpcList() as $player) {
            $this->delete($player);
        }
    }

    private function delete(User $user): void
    {
        $userId = $user->getId();
        $name = $this->bbCodeParser->parse($user->getName())->getAsText();
        $delmark = $user->getRegistration()->getDeletionMark();

        $this->loggerUtil->log(sprintf('deleting userId: %d', $userId));

        array_walk(
            $this->deletionHandlers,
            function (PlayerDeletionHandlerInterface $handler) use ($user): void {
                $handler->delete($user);
            }
        );

        $this->loggerUtil->log(sprintf('deleted user (id: %d, name: %s, delmark: %d)', $userId, $name, $delmark));
    }
}
