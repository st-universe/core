<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Playerlist;

use Stu\Module\Admin\Lib\UserlistEntry;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Playerlist implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PLAYER_LIST';

    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
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
        $game->setTemplateFile('html/admin/playerList.twig');
        $game->setPageTitle(_('Spielerliste'));
        $game->setTemplateVar(
            'LIST',
            array_map(
                fn (UserInterface $user): UserlistEntry => new UserlistEntry($user),
                $this->userRepository->getNonNpcList()
            )
        );
    }
}
