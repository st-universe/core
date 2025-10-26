<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\CommoditiesOverview;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Module\Database\Lib\StorageWrapper;
use Stu\Orm\Repository\StorageRepositoryInterface;

/**
 * Shows a list of all available commodities and their amount
 */
final class CommoditiesOverview implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COMMODITIES_OVERVIEW';

    public function __construct(private StorageRepositoryInterface $storageRepository, private DatabaseUiFactoryInterface $databaseUiFactory)
    {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setNavigation([
            [
                'url' => 'database.php',
                'title' => 'Datenbank'
            ],
            [
                'url' => sprintf('database.php?%s=1', self::VIEW_IDENTIFIER),
                'title' => 'Warenübersicht'
            ]
        ]);

        $game->setPageTitle('/ Datenbank / Warenübersicht');
        $game->setViewTemplate('html/database/commoditiesOverview.twig');
        $game->setTemplateVar(
            'COMMODITIES_LIST',
            array_map(
                fn (array $storage): StorageWrapper => $this->databaseUiFactory->createStorageWrapper(
                    $storage['commodity_id'],
                    $storage['amount']
                ),
                $this->storageRepository->getByUserAccumulated($game->getUser())
            )
        );
    }
}
