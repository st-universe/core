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
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;

class PirateWrathManager implements PirateWrathManagerInterface
{
    public const MINIMUM_WRATH = 500;
    public const MAXIMUM_WRATH = 2000;

    private PirateLoggerInterface $logger;

    public function __construct(
        private PirateWrathRepositoryInterface $pirateWrathRepository,
        private StuRandom $stuRandom,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private StuTime $stuTime,
        private PrivateMessageSenderInterface $privateMessageSender,
        LoggerUtilFactoryInterface $loggerUtilFactory,
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

        $reactionTriggerWrath = $reactionTrigger->getWrath();
        if ($reactionTriggerWrath < 1) {
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

        // increase wrath
        $currentWrath = $wrath->getWrath();
        $wrath->setWrath($currentWrath + $reactionTriggerWrath);

        // reset protection timeout
        $timeout = $wrath->getProtectionTimeout();
        if (
            $timeout !== null
            && $timeout > time()
        ) {
            $this->makePiratesReallyAngry($wrath);
        } else {

            $this->logger->logf(
                'INCREASED wrath of user %d from %d to %d',
                $user->getId(),
                $currentWrath,
                $wrath->getWrath()
            );
        }

        $this->pirateWrathRepository->save($wrath);
    }

    private function makePiratesReallyAngry(PirateWrathInterface $wrath): void
    {
        $user = $wrath->getUser();

        $this->logger->logf(
            'RESET protection timeout of user %d and set wrath to MAXIMUM of %d',
            $user->getId(),
            self::MAXIMUM_WRATH
        );
        $wrath->setProtectionTimeout(null);
        $wrath->setWrath(self::MAXIMUM_WRATH);

        $this->privateMessageSender->send(
            UserEnum::USER_NPC_KAZON,
            $user->getId(),
            sprintf('Wie kannst du es wagen? Ich werde meine Horden auf dich hetzen bis du winselnd am Boden liegst! Der Nichtangriffspakt ist hinfällig!'),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN
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

        // 1 Prestige = 2.88 Stunden = 10368 Sekunden
        $baseTimeoutInSeconds = max(1, ((1 / $wrathFactor) ** 2) * ($prestige * 10368));
        $minTimeout = $baseTimeoutInSeconds * 0.95;
        $maxTimeout = $baseTimeoutInSeconds * 1.05;

        $timestamp = $this->stuRandom->rand((int)$minTimeout, (int)$maxTimeout);

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
