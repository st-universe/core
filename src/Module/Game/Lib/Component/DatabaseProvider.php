<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;

final class DatabaseProvider implements ViewComponentProviderInterface
{
    private DatabaseCategoryRepositoryInterface $databaseCategoryRepository;

    public function __construct(
        DatabaseCategoryRepositoryInterface $databaseCategoryRepository
    ) {
        $this->databaseCategoryRepository = $databaseCategoryRepository;
    }

    public function setTemplateVariables(GameControllerInterface $game): void
    {
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
