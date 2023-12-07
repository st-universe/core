<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowUserLock;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;

final class ShowUserLock implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_USER_LOCK';

    private UserLockRepositoryInterface $userLockRepository;

    public function __construct(
        UserLockRepositoryInterface $userLockRepository
    ) {
        $this->userLockRepository = $userLockRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userIdToLock = request::getIntFatal('uid');

        $game->setPageTitle(_('User-Lock setzen'));
        $game->setMacroInAjaxWindow('html/admin/userLock.twig');

        $game->setTemplateVar('USERID', $userIdToLock);
        $game->setTemplateVar('LOCK', $this->userLockRepository->getActiveByUser($userIdToLock));
    }
}
