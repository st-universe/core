<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\GoodsOverview;

use Stu\Lib\StorageWrapper\StorageWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;

final class GoodsOverview implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_GOODS_OVERVIEW';

    private ColonyStorageRepositoryInterface $colonyStorageRepository;

    private ShipStorageRepositoryInterface $shipStorageRepository;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private TradeStorageRepositoryInterface $tradeStorageRepository;

    public function __construct(
        ColonyStorageRepositoryInterface $colonyStorageRepository,
        ShipStorageRepositoryInterface $shipStorageRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository
    ) {
        $this->colonyStorageRepository = $colonyStorageRepository;
        $this->shipStorageRepository = $shipStorageRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
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
            } else {
                $goodsOverview[$data['commodity_id']] = new StorageWrapper($data['commodity_id'], $data['amount']);
            }
        }

        // add storage of trade posts
        $tradepostsStorage = $this->tradeStorageRepository->getByUserAccumulated($game->getUser()->getId());
        foreach ($tradepostsStorage as $data) {
            if (array_key_exists($data['commodity_id'], $goodsOverview)) {
                $storageWrapper = $goodsOverview[$data['commodity_id']];
                $storageWrapper->addAmount($data['amount']);
            } else {
                $goodsOverview[$data['commodity_id']] = new StorageWrapper($data['commodity_id'], $data['amount']);
            }
        }

        // add storage of trade offers
        $tradeoffersStorage = $this->tradeOfferRepository->getByUserAccumulated($game->getUser()->getId());
        foreach ($tradeoffersStorage as $data) {
            if (array_key_exists($data['commodity_id'], $goodsOverview)) {
                $storageWrapper = $goodsOverview[$data['commodity_id']];
                $storageWrapper->addAmount($data['amount']);
            } else {
                $goodsOverview[$data['commodity_id']] = new StorageWrapper($data['commodity_id'], $data['amount']);
            }
        }

        usort(
            $goodsOverview,
            function (StorageWrapper $a, StorageWrapper $b): int {
                if ($a->getCommodity()->getSort() == $b->getCommodity()->getSort()) {
                    return 0;
                }
                return ($a->getCommodity()->getSort() < $b->getCommodity()->getSort()) ? -1 : 1;
            }
        );

        $game->setTemplateVar('GOODS_LIST', $goodsOverview);
    }
}
