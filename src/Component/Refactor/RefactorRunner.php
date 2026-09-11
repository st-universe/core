<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\UserCrewRaceRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class RefactorRunner
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CrewRaceRepositoryInterface $crewRaceRepository,
        private UserCrewRaceRepositoryInterface $userCrewRaceRepository
    ) {}

    public function refactor(): void
    {
        foreach ($this->userRepository->findAll() as $user) {
            $this->refactorUser($user);
        }
    }

    private function refactorUser(User $user): void
    {
        foreach ($this->userCrewRaceRepository->getByUserId($user->getId()) as $userCrewRace) {
            if ($userCrewRace->getChance() === null) {
                $userCrewRace->setChance($userCrewRace->getCrewRace()->getChance());
            }
            $this->userCrewRaceRepository->save($userCrewRace);
        }

        foreach ($this->crewRaceRepository->getStandardForFaction($user->getFactionId()) as $crewRace) {
            if ($this->userCrewRaceRepository->exists($crewRace->getId(), $user->getId())) {
                continue;
            }

            $this->userCrewRaceRepository->save(
                $this->userCrewRaceRepository->prototype()
                    ->setCrewRace($crewRace)
                    ->setUserId($user->getId())
                    ->setChance($crewRace->getChance())
            );
        }
    }
}
