<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\NextTutorial;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\UserRepositoryInterface;

final class NextTutorial implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TUTORIAL_NEXT';

    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        //if ((int) $user->getState() !== UserEnum::USER_STATE_TUTORIAL1 || (int) $user->getState() !== UserEnum::USER_STATE_TUTORIAL2 || (int) $user->getState() !== UserEnum::USER_STATE_TUTORIAL3 || (int) $user->getState() !== UserEnum::USER_STATE_TUTORIAL4) {
        //          throw new AccessViolation();
        //    }

        if ((int) $user->getState() === UserEnum::USER_STATE_TUTORIAL1) {
            $user->setState(UserEnum::USER_STATE_TUTORIAL2);
        }

        if ((int) $user->getState() === UserEnum::USER_STATE_TUTORIAL2) {
            $user->setState(UserEnum::USER_STATE_TUTORIAL3);
        }

        if ((int) $user->getState() === UserEnum::USER_STATE_TUTORIAL3) {
            $user->setState(UserEnum::USER_STATE_TUTORIAL4);
        }

        if ((int) $user->getState() === UserEnum::USER_STATE_TUTORIAL4) {
            $user->setState(UserEnum::USER_STATE_ACTIVE);
        }

        $this->userRepository->save($user);
    }


    public function performSessionCheck(): bool
    {
        return false;
    }
}
