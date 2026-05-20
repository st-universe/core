<?php

declare(strict_types=1);

namespace Stu\Module\Award\Lib;

use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\Award;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserAward;
use Stu\Orm\Repository\UserAwardRepositoryInterface;

final class CreateUserAward implements CreateUserAwardInterface
{
    public function __construct(private UserAwardRepositoryInterface $userAwardRepository, private CreatePrestigeLogInterface $createPrestigeLog) {}

    #[\Override]
    public function createAwardForUser(User $user, Award $award, bool $incrementExisting = false): void
    {
        /** @var UserAward|null $existingAward */
        $existingAward = $this->userAwardRepository->findOneBy([
            'user_id' => $user->getId(),
            'award_id' => $award->getId()
        ]);

        if ($existingAward !== null) {
            if ($incrementExisting) {
                $existingAward->setCount(($existingAward->getCount() ?? 1) + 1);
                $this->userAwardRepository->save($existingAward);
                $this->createPrestigeLogForAward($award, $user);
            } elseif ($existingAward->getCount() === null) {
                $existingAward->setCount(1);
                $this->userAwardRepository->save($existingAward);
            }
            return;
        }

        $userAward = $this->userAwardRepository->prototype();
        $userAward->setUser($user);
        $userAward->setAward($award);
        $userAward->setCount(1);

        $this->userAwardRepository->save($userAward);

        $this->createPrestigeLogForAward($award, $user);
    }

    private function createPrestigeLogForAward(Award $award, User $user): void
    {
        if ($award->getPrestige() === 0) {
            return;
        }

        $this->createPrestigeLog->createLog(
            $award->getPrestige(),
            sprintf('%d Prestige erhalten für den Erhalt des Awards "%s"', $award->getPrestige(), $award->getDescription()),
            $user,
            time()
        );
    }
}
