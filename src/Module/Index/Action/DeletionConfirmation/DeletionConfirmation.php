<?php

declare(strict_types=1);

namespace Stu\Module\Index\Action\DeletionConfirmation;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\UserRepositoryInterface;

final class DeletionConfirmation implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'CONFIRM_ACCOUNT_DELETION';

    private DeletionConfirmationRequestInterface $deletionConfirmationRequest;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        DeletionConfirmationRequestInterface $deletionConfirmationRequest,
        UserRepositoryInterface $userRepository
    ) {
        $this->deletionConfirmationRequest = $deletionConfirmationRequest;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $token = $this->deletionConfirmationRequest->getToken();

        $user = $this->userRepository->getByResetToken($token);

        if ($user === null || $user->getDeletionMark() !== UserEnum::DELETION_REQUESTED) {
            return;
        }

        $user->setPasswordToken('');
        $user->setDeletionMark(UserEnum::DELETION_CONFIRMED);

        $this->userRepository->save($user);

        $game->addInformation(_('Dein Account wurde endgültig zur Löschung vorgesehen. Ein Login ist nicht mehr möglich.'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
