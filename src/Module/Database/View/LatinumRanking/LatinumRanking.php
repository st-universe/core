<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\LatinumRanking;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopLatinum;
use Stu\Orm\Repository\UserRepositoryInterface;

final class LatinumRanking implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_TOP_LATINUM';

    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            'database.php',
            _('Datenbank')
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Die 10 Söhne des Nagus')
        );
        $game->setPageTitle(_('/ Datenbank / Die 10 Söhne des Nagus'));
        $game->showMacro('html/database.xhtml/top_lat_user');

        $game->setTemplateVar('NAGUS_LIST', $this->getTop10());
    }

    private function getTop10()
    {
        return array_map(
            function (array $data): DatabaseTopLatinum {
                return new DatabaseTopLatinum($data);
            },
            $this->userRepository->getLatinumTop10()
        );
    }
}
