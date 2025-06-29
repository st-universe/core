<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowUserLock;

use Override;
use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowUserLock implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_USER_LOCK';

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserLockRepositoryInterface $userLockRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userIdToLock = request::getIntFatal('id');

        $game->setPageTitle(_('User-Lock setzen'));
        $game->setMacroInAjaxWindow('html/admin/userLock.twig');

        $user = $this->userRepository->find($userIdToLock);
        if ($user === null) {
            throw new SanityCheckException(sprintf('userId %d does not exist', $userIdToLock));
        }

        $game->setTemplateVar('USERID', $userIdToLock);
        $game->setTemplateVar('LOCK', $this->userLockRepository->getActiveByUser($user));
    }
}
