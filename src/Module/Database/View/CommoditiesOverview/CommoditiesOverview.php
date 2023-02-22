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
    public const VIEW_IDENTIFIER = 'SHOW_COMMODITIES_OVERVIEW';

    private StorageRepositoryInterface $storageRepository;

    private DatabaseUiFactoryInterface $databaseUiFactory;

    public function __construct(
        StorageRepositoryInterface $storageRepository,
        DatabaseUiFactoryInterface $databaseUiFactory
    ) {
        $this->storageRepository = $storageRepository;
        $this->databaseUiFactory = $databaseUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setNavigation([
            [
                'url' => 'database.php',
                'title' => 'Datenbank',
            ],
            [
                'url' => sprintf('database.php?%s=1', self::VIEW_IDENTIFIER),
                'title' => 'Warenübersicht',
            ],
        ]);

        $game->setPageTitle('/ Datenbank / Warenübersicht');
        $game->showMacro('html/database.xhtml/commodities_overview');
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
