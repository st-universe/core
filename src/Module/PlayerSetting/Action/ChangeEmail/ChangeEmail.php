<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeEmail;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ChangeEmail implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_EMAIL';

    private ChangeEmailRequestInterface $changeEmailRequest;

    private UserRepositoryInterface $userRepository;

    private BlockedUserRepositoryInterface $blockedUserRepository;

    private StuHashInterface $stuHash;

    public function __construct(
        ChangeEmailRequestInterface $changeEmailRequest,
        UserRepositoryInterface $userRepository,
        BlockedUserRepositoryInterface $blockedUserRepository,
        StuHashInterface $stuHash
    ) {
        $this->changeEmailRequest = $changeEmailRequest;
        $this->userRepository = $userRepository;
        $this->blockedUserRepository = $blockedUserRepository;
        $this->stuHash = $stuHash;
    }

    public function handle(GameControllerInterface $game): void
    {
        $value = trim($this->changeEmailRequest->getEmailAddress());
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $game->addInformation(_('Die E-Mailadresse ist ungültig'));
            return;
        }

        if ($this->userRepository->getByEmail($value)) {
            $game->addInformation(_('Die E-Mailadresse wird bereits verwendet'));
            return;
        }
        if ($this->blockedUserRepository->getByEmailHash($this->stuHash->hash($value))) {
            $game->addInformation(_('Die E-Mailadresse ist blockiert'));
            return;
        }

        $user = $game->getUser();

        $user->setEmail($value);

        $this->userRepository->save($user);

        $game->addInformation(_('Deine E-Mailadresse wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
