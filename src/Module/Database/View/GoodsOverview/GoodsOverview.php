<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\GoodsOverview;

use Stu\Lib\StorageWrapper\StorageWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;

final class GoodsOverview implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_GOODS_OVERVIEW';

    private ColonyStorageRepositoryInterface $colonyStorageRepository;
   
    private ShipStorageRepositoryInterface $shipStorageRepository;

    public function __construct(
        ColonyStorageRepositoryInterface $colonyStorageRepository,
        ShipStorageRepositoryInterface $shipStorageRepository
    ) {
        $this->colonyStorageRepository = $colonyStorageRepository;
        $this->shipStorageRepository = $shipStorageRepository;
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

        $goodsOverview = [];
        
        // add storage of colonies
        $coloniesStorage = $this->colonyStorageRepository->getByUserAccumulated($game->getUser()->getId());
        foreach ($coloniesStorage as $data) {
            $goodsOverview[$data['commodity_id']] = new StorageWrapper($data['commodity_id'], $data['amount']);
        }
        
        // add storage of ships
        $shipsStorage = $this->shipStorageRepository->getByUserAccumulated($game->getUser()->getId());
        foreach ($shipsStorage as $data) {
            if (array_key_exists($data['commodity_id'], $goodsOverview)) {
                $storageWrapper = $goodsOverview[$data['commodity_id']];
                $storageWrapper->addAmount($data['amount']);
            }
            else {
                $goodsOverview[$data['commodity_id']] = new StorageWrapper($data['commodity_id'], $data['amount']);
            }
        }


        // add storage of trade posts
        $game->setTemplateVar('GOODS_LIST', $goodsOverview);
    }
}
