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
    public const MINIMUM_WRATH = 500;
    public const MAXIMUM_WRATH = 2000;

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
            $user->setPirateWrath($wrath);
        }

        if ($wrath->getWrath() >= self::MAXIMUM_WRATH) {
            $this->logger->logf(
                'MAXIMUM_WRATH = %d of user %d already reached',
                self::MAXIMUM_WRATH,
                $user->getId()
            );
            return;
        }

        // reset protection timeout
        $timeout = $wrath->getProtectionTimeout();
        if (
            $timeout !== null
            && $timeout > time()
        ) {
            $this->logger->logf(
                'RESET protection timeout of user %d',
                $user->getId(),
            );
            $wrath->setProtectionTimeout(null);
        }

        // increase wrath
        $currentWrath = $wrath->getWrath();
        $wrath->setWrath($currentWrath + $reactionTrigger->getWrath());
        $this->pirateWrathRepository->save($wrath);

        $this->logger->logf(
            'INCREASED wrath of user %d from %d to %d',
            $user->getId(),
            $currentWrath,
            $wrath->getWrath()
        );
    }

    public function decreaseWrath(UserInterface $user, int $amount): void
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
            $user->setPirateWrath($wrath);
        }

        if ($wrath->getWrath() <= self::MINIMUM_WRATH) {
            $this->logger->logf(
                'MINIMUM_WRATH = %d of user %d already reached',
                self::MINIMUM_WRATH,
                $user->getId()
            );
            return;
        }

        // decrease wrath
        $currentWrath = $wrath->getWrath();
        $wrath->setWrath($currentWrath - $amount);
        $this->pirateWrathRepository->save($wrath);

        $this->logger->logf(
            'DECREASED wrath of user %d from %d to %d',
            $user->getId(),
            $currentWrath,
            $wrath->getWrath()
        );
    }
}
