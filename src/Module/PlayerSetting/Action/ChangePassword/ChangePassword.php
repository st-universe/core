<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangePassword;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ChangePassword implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_PASSWORD';

    /**
     * @todo Extract into a separate password validator
     */
    public const string PASSWORD_REGEX = '/[a-zA-Z0-9]{6,20}/';

    public function __construct(private ChangePasswordRequestInterface $changePasswordRequest, private UserRepositoryInterface $userRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $user = $context->getUser();
        $currentPassword = $this->changePasswordRequest->getCurrentPassword();

        if ($currentPassword === '') {
            $context->getInfo()->addInformation(_('Das alte Passwort wurde nicht angegeben'));
            return;
        }
        if (!password_verify($currentPassword, $user->getRegistration()->getPassword())) {
            $context->getInfo()->addInformation(_('Das alte Passwort ist falsch'));
            return;
        }

        $newPassword = trim($this->changePasswordRequest->getNewPassword());
        $newPasswordReEntered = $this->changePasswordRequest->getNewPasswordReEntered();

        if ($newPassword === '') {
            $context->getInfo()->addInformation(_('Es wurde kein neues Passwort eingegeben'));
            return;
        }
        if (!preg_match(self::PASSWORD_REGEX, $newPassword)) {
            $context->getInfo()->addInformation(_('Das Passwort darf nur aus Zahlen und Buchstaben bestehen und muss zwischen 6 und 20 Zeichen lang sein'));
            return;
        }
        if ($newPassword !== $newPasswordReEntered) {
            $context->getInfo()->addInformation(_('Die eingegebenen Passwörter stimmen nicht überein'));
            return;
        }
        $user->getRegistration()->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));

        $this->userRepository->save($user);

        $context->getInfo()->addInformation(_('Das Passwort wurde geändert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
