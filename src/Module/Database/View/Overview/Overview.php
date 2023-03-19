<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\Overview;

use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private DatabaseCategoryRepositoryInterface $databaseCategoryRepository;

    public function __construct(
        DatabaseCategoryRepositoryInterface $databaseCategoryRepository
    ) {
        $this->databaseCategoryRepository = $databaseCategoryRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            'database.php',
            _('Datenbank')
        );
        $game->setPageTitle(_('/ Datenbank'));
        $game->setTemplateFile('html/database.xhtml');

        $game->setTemplateVar(
            'RUMP_LIST',
            $this->databaseCategoryRepository->getByTypeId(DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP)
        );
        $game->setTemplateVar(
            'RPG_SHIP_LIST',
            $this->databaseCategoryRepository->getByTypeId(DatabaseEntryTypeEnum::DATABASE_TYPE_RPGSHIPS)
        );
        $game->setTemplateVar(
            'POI_LIST',
            $this->databaseCategoryRepository->getByTypeId(DatabaseEntryTypeEnum::DATABASE_TYPE_POI)
        );
        $game->setTemplateVar(
            'MAP_LIST',
            $this->databaseCategoryRepository->getByTypeId(DatabaseEntryTypeEnum::DATABASE_TYPE_MAP)
        );
    }
}
