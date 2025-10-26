<?php

namespace Stu\Lib\Pirate\Component;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\PirateRoundRepositoryInterface;
use Stu\Orm\Repository\UserPirateRoundRepositoryInterface;

class PirateRoundManager implements PirateRoundManagerInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private PirateRoundRepositoryInterface $pirateRoundRepository,
        private UserPirateRoundRepositoryInterface $userPirateRoundRepository,
        private EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory,
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[\Override]
    public function decreasePrestige(int $amount): void
    {
        if ($amount < 1) {
            return;
        }

        $currentRound = $this->pirateRoundRepository->getCurrentActiveRound();
        if ($currentRound === null) {
            return;
        }

        $currentPrestige = $currentRound->getActualPrestige();
        $newPrestige = max(0, $currentPrestige - $amount);

        $currentRound->setActualPrestige($newPrestige);

        if ($newPrestige === 0) {
            $currentRound->setEndTime(time());
        }

        $this->pirateRoundRepository->save($currentRound);

        $this->logger->logf(
            'DECREASED pirate round prestige from %d to %d',
            $currentPrestige,
            $newPrestige
        );
    }

    #[\Override]
    public function addUserStats(User $user, int $prestige): void
    {
        $currentRound = $this->pirateRoundRepository->getCurrentActiveRound();
        if ($currentRound === null) {
            return;
        }

        $userPirateRound = $this->userPirateRoundRepository->findByUserAndPirateRound(
            $user->getId(),
            $currentRound->getId()
        );

        if ($userPirateRound === null) {
            $userPirateRound = $this->userPirateRoundRepository->prototype();
            $userPirateRound->setUser($user);
            $userPirateRound->setPirateRound($currentRound);
            $userPirateRound->setDestroyedShips(1);
            $userPirateRound->setPrestige($prestige);
            $this->userPirateRoundRepository->save($userPirateRound);
            $this->entityManager->flush();
        } else {
            $userPirateRound->setDestroyedShips($userPirateRound->getDestroyedShips() + 1);
            $userPirateRound->setPrestige($userPirateRound->getPrestige() + $prestige);
            $this->userPirateRoundRepository->save($userPirateRound);
        }


        $this->logger->logf(
            'ADDED user stats for user %d: ships=%d, prestige=%d',
            $user->getId(),
            $userPirateRound->getDestroyedShips(),
            $userPirateRound->getPrestige()
        );
    }
}
