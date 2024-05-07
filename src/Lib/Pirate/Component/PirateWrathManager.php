<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\PirateWrathInterface;
use Stu\Orm\Repository\PirateWrathRepositoryInterface;
use Stu\Module\Control\GameControllerInterface;

class PirateWrathManager implements PirateWrathManagerInterface
{
    public const MINIMUM_WRATH = 500;
    public const MAXIMUM_WRATH = 2000;

    private PirateLoggerInterface $logger;

    private CreatePrestigeLogInterface $createPrestigeLog;

    private StuRandom $stuRandom;

    private StuTime $stuTime;

    public function __construct(
        private PirateWrathRepositoryInterface $pirateWrathRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        StuRandom $stuRandom,
        CreatePrestigeLogInterface $createPrestigeLog,
        StuTime $stuTime
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
        $this->createPrestigeLog = $createPrestigeLog;
        $this->stuRandom = $stuRandom;
        $this->stuTime = $stuTime;
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

        $wrath = $this->getPirateWrathOfUser($user);

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

    public function setProtectionTimeoutFromPrestige(UserInterface $user, int $prestige, GameControllerInterface $game): void
    {
        $wrath = $this->getPirateWrathOfUser($user);

        $wrathFactor = $wrath->getWrath() / PirateWrathInterface::DEFAULT_WRATH;

        // 1 Prestige = 1.44 Stunden = 5184 Sekunden
        $timeoutInSeconds = max(1, ((1 / $wrathFactor) ** 2) * ($prestige * 5184));

        $randomFactor = $this->stuRandom->rand(95, 105) / 100;
        $timestamp = (int)($timeoutInSeconds * $randomFactor);

        $currentTimeout = $wrath->getProtectionTimeout();
        if ($currentTimeout !== null && $currentTimeout > time()) {
            $timestamp += $currentTimeout;
        } else {
            $timestamp += time();
        }

        $wrath->setProtectionTimeout($timestamp);
        $this->pirateWrathRepository->save($wrath);

        $this->createPrestigeLog->createLog(
            -$prestige,
            sprintf('-%d Prestige: Großer Nagus garantiert Schutz vor Piraten bis zum %s', $prestige, $this->stuTime->transformToStuDate($timestamp)),
            $user,
            time()
        );


        $game->addInformation(sprintf(
            _('Der Nagus konnte einen Nichtangriffspakt mit den Kazon bis zum %s Uhr aushandeln'),
            $this->stuTime->transformToStuDate($timestamp)
        ));
    }

    private function getPirateWrathOfUser(UserInterface $user): PirateWrathInterface
    {
        $wrath = $user->getPirateWrath();

        if ($wrath === null) {
            $wrath = $this->pirateWrathRepository->prototype();
            $wrath->setUser($user);
            $user->setPirateWrath($wrath);
        }

        return $wrath;
    }
}
