<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\LatinumRanking;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopLatinum;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class LatinumRanking implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TOP_LATINUM';

    private StorageRepositoryInterface $storageRepository;

    public function __construct(
        StorageRepositoryInterface $storageRepository
    ) {
        $this->storageRepository = $storageRepository;
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
            $this->storageRepository->getLatinumTop10()
        );
    }
}
