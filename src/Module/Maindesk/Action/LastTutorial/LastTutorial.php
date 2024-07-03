<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\LastTutorial;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\UserRepositoryInterface;

final class LastTutorial implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TUTORIAL_BACK';

    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if (!$user->hasColony()) {
            throw new AccessViolation();
        }

        if ($user->getState() === UserEnum::USER_STATE_TUTORIAL2) {
            $user->setState(UserEnum::USER_STATE_TUTORIAL1);
        } elseif ($user->getState() === UserEnum::USER_STATE_TUTORIAL3) {
            $user->setState(UserEnum::USER_STATE_TUTORIAL2);
        } elseif ($user->getState() === UserEnum::USER_STATE_TUTORIAL4) {
            $user->setState(UserEnum::USER_STATE_TUTORIAL3);
        }

        $this->userRepository->save($user);
    }


    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
