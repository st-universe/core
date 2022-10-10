<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\CommoditiesOverview;

use Stu\Lib\StorageWrapper\StorageWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class CommoditiesOverview implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_COMMODITIES_OVERVIEW';

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
            _('Warenübersicht')
        );
        $game->setPageTitle(_('/ Datenbank / Warenübersicht'));
        $game->showMacro('html/database.xhtml/commodities_overview');

        $commoditiesOverview = [];

        $storages = $this->storageRepository->getByUserAccumulated($game->getUser()->getId());
        foreach ($storages as $storage) {
            $commoditiesOverview[$storage['commodity_id']] = new StorageWrapper($storage['commodity_id'], $storage['amount']);
        }

        $game->setTemplateVar('COMMODITIES_LIST', $commoditiesOverview);
    }
}
