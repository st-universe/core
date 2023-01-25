<?php

declare(strict_types=1);

namespace Stu\Module\Award\Lib;

use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\AwardInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserAwardRepositoryInterface;

final class CreateUserAward implements CreateUserAwardInterface
{
    private UserAwardRepositoryInterface $userAwardRepository;

    private CreatePrestigeLogInterface $createPrestigeLog;

    public function __construct(
        UserAwardRepositoryInterface $userAwardRepository,
        CreatePrestigeLogInterface $createPrestigeLog
    ) {
        $this->userAwardRepository = $userAwardRepository;
        $this->createPrestigeLog = $createPrestigeLog;
    }

    public function createAwardForUser(UserInterface $user, AwardInterface $award): void
    {
        //check if user already has award in case of category updates
        if ($user->hasAward($award->getId())) {
            return;
        }

        $userAward = $this->userAwardRepository->prototype();
        $userAward->setUser($user);
        $userAward->setAward($award);

        $this->userAwardRepository->save($userAward);

        //create prestige log
        $description = sprintf('%d Prestige erhalten für den Erhalt des Awards "%s"', $award->getPrestige(), $award->getDescription());

        if ($award->getPrestige() !== 0) {
            $this->createPrestigeLog->createLog($award->getPrestige(), $description, $user, time());
        }
    }
}
