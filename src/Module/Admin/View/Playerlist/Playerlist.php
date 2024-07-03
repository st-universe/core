<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Playerlist;

use Override;
use Stu\Module\Admin\Lib\UserlistEntry;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Playerlist implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PLAYER_LIST';

    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    #[Override]
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
