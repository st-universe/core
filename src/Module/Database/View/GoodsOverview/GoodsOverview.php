<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\GoodsOverview;

use Stu\Lib\StorageWrapper\StorageWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class GoodsOverview implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_GOODS_OVERVIEW';

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
        $game->showMacro('html/database.xhtml/goods_overview');

        $goodsOverview = [];

        $storages = $this->storageRepository->getByUserAccumulated($game->getUser()->getId());
        foreach ($storages as $storage) {
            $goodsOverview[$storage['commodity_id']] = new StorageWrapper($storage['commodity_id'], $storage['amount']);
        }

        $game->setTemplateVar('GOODS_LIST', $goodsOverview);
    }
}
