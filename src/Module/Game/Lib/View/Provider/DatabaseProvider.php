<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Component\Image\ImageCreationInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;

final class DatabaseProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private readonly DatabaseCategoryRepositoryInterface $databaseCategoryRepository,
        private readonly ImageCreationInterface $imageCreation,
        private readonly StuConfigInterface $config
    )
    {
    }

    #[\Override]
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

        // load event map from file
        $historyFolder = $this->config->getGameSettings()->getTempDir() . '/history';
        $graph = imagecreatefrompng($historyFolder . '/layer_2.png');
        if ($graph === false) {
            throw new \InvalidArgumentException('error creating event map image from file');
        }

        $game->setTemplateVar(
            'HISTORY_EVENT_MAP',
            $this->imageCreation->gdImageInSrc($graph)
        );
    }
}
