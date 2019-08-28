<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangePassword;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use User;

final class ChangePassword implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_PASSWORD';

    private $changePasswordRequest;

    public function __construct(
        ChangePasswordRequestInterface $changePasswordRequest
    ) {
        $this->changePasswordRequest = $changePasswordRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $currentPassword = $this->changePasswordRequest->getCurrentPassword();

        if (!$currentPassword) {
            $game->addInformation(_('Das alte Passwort wurde nicht angegeben'));
            return;
        }
        if (User::hashPassword($currentPassword) !== $user->getPassword()) {
            $game->addInformation(_('Das alte Passwort ist falsch'));
            return;
        }

        $newPassword = $this->changePasswordRequest->getNewPassword();
        $newPasswordReEntered = $this->changePasswordRequest->getNewPasswordReEntered();

        if (!$newPassword) {
            $game->addInformation(_('Es wurde kein neues Passwort eingegeben'));
            return;
        }
        if (!preg_match('/[a-zA-Z0-9]{6,20}/', $newPassword)) {
            $game->addInformation(_('Das Passwort darf nur aus Zahlen und Buchstaben bestehen und muss zwischen 6 und 20 Zeichen lang sein'));
            return;
        }
        if ($newPassword !== $newPasswordReEntered) {
            $game->addInformation(_('Die eingegebenen Passwörter stimmen nichberein'));
            return;
        }
        $user->setPassword(User::hashPassword($newPassword));
        $user->save();

        $game->addInformation(_('Das Passwort wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
