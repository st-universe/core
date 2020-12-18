<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\GoodsOverview;

use Stu\Lib\StorageWrapper\StorageWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;

final class GoodsOverview implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_GOODS_OVERVIEW';

    private ColonyStorageRepositoryInterface $colonyStorageRepository;

    public function __construct(
        ColonyStorageRepositoryInterface $colonyStorageRepository
    ) {
        $this->colonyStorageRepository = $colonyStorageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Warenübersicht')
        );
        $game->setPageTitle(_('/ Datenbank / Warenübersicht'));
        $game->showMacro('html/database.xhtml/goods_overview');

        $coloniesStorage = $this->colonyStorageRepository->getByUserAccumulated($game->getUser()->getId());

        $goodsOverview = [];
        foreach ($coloniesStorage as $data) {
            $goodsOverview[] = new StorageWrapper($data['commodity_id'], $data['amount']);
        }

        $game->setTemplateVar('GOODS_LIST', $goodsOverview);
    }
}
