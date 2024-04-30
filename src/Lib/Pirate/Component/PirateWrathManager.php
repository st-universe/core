<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\PirateWrathRepositoryInterface;

class PirateWrathManager implements PirateWrathManagerInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private PirateWrathRepositoryInterface $pirateWrathRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function increaseWrath(UserInterface $user, PirateReactionTriggerEnum $reactionTrigger): void
    {
        if (
            $user->isNpc()
            || $user->getId() === UserEnum::USER_NPC_KAZON
        ) {
            return;
        }

        $wrath = $user->getPirateWrath();
        if ($wrath === null) {
            $wrath = $this->pirateWrathRepository->prototype();
            $wrath->setUser($user);
        }

        // reset protection timeout
        $timeout = $wrath->getProtectionTimeout();
        if (
            $timeout !== null
            && $timeout > time()
        ) {
            $this->logger->logf(
                '    reset protection timeout of user %d',
                $user->getId(),
            );
            $wrath->setProtectionTimeout(null);
        }

        // increase wrath
        $currentWrath = $wrath->getWrath();
        $wrath->setWrath($currentWrath + $reactionTrigger->getWrath());

        $this->logger->logf(
            '    increased wrath of user %d from %d to %d',
            $user->getId(),
            $currentWrath,
            $wrath->getWrath()
        );
    }
}
