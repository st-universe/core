<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowResetPassword;

use InvalidParamException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use User;

final class ShowResetPassword implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_RESET_PASSWORD';

    private $showResetPasswordRequest;

    public function __construct(
        ShowResetPasswordRequestInterface $showResetPasswordRequest
    ) {
        $this->showResetPasswordRequest = $showResetPasswordRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = User::getByPasswordResetToken($this->showResetPasswordRequest->getToken());
        if ($user === false) {
            throw new InvalidParamException;
        }
        $game->setTemplateFile('html/index_resetpassword.xhtml');
        $game->setPageTitle(_('Password zurücksetzen - Star Trek Universe'));
        $game->setTemplateVar('TOKEN', $user->getPasswordToken());
    }
}
