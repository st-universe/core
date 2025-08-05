<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeEmail;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Orm\Repository\BlockedUserRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ChangeEmail implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_EMAIL';

    public function __construct(private ChangeEmailRequestInterface $changeEmailRequest, private UserRepositoryInterface $userRepository, private BlockedUserRepositoryInterface $blockedUserRepository, private StuHashInterface $stuHash) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $value = trim($this->changeEmailRequest->getEmailAddress());
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $game->getInfo()->addInformation(_('Die E-Mailadresse ist ungültig'));
            return;
        }

        if ($this->userRepository->getByEmail($value) !== null) {
            $game->getInfo()->addInformation(_('Die E-Mailadresse wird bereits verwendet'));
            return;
        }
        if ($this->blockedUserRepository->getByEmailHash($this->stuHash->hash($value)) !== null) {
            $game->getInfo()->addInformation(_('Die E-Mailadresse ist blockiert'));
            return;
        }

        $user = $game->getUser();

        $user->getRegistration()->setEmail($value);

        $this->userRepository->save($user);

        $game->getInfo()->addInformation(_('Deine E-Mailadresse wurde geändert'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
