<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowResetPassword;

use Stu\Exception\InvalidParamException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowResetPassword implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_RESET_PASSWORD';

    public function __construct(
        private ShowResetPasswordRequestInterface $showResetPasswordRequest,
        private UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $this->userRepository->getByResetToken($this->showResetPasswordRequest->getToken());
        if ($user === null) {
            throw new InvalidParamException(sprintf(
                'Could not get user by reset token, userId: %d',
                $game->getUser()->getId()
            ));
        }
        $game->setTemplateFile('html/index/resetPassword.twig');
        $game->setPageTitle(_('Password zurücksetzen - Star Trek Universe'));
        $game->setTemplateVar('TOKEN', $user->getRegistration()->getPasswordToken());
    }
}
