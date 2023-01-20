<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Playerlist;

use Stu\Module\Admin\Lib\UserlistEntry;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\UserTagRepositoryInterface;

final class Playerlist implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PLAYER_LIST';

    private UserRepositoryInterface $userRepository;

    private UserTagRepositoryInterface $userTagRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserTagRepositoryInterface $userTagRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userTagRepository = $userTagRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                '/admin/?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Spielerliste')
        );
        $game->setTemplateFile('html/admin/playerlist.xhtml');
        $game->setPageTitle(_('Spielerliste'));
        $game->setTemplateVar(
            'LIST',
            array_map(
                function (UserInterface $user): UserlistEntry {
                    return new UserlistEntry($this->userTagRepository, $user);
                },
                $this->userRepository->getNonNpcList()
            )
        );
    }
}
