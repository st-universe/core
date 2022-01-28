<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowGoodsLocations;

use Stu\Lib\StorageWrapper\StorageWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyStorageRepositoryInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;

final class ShowGoodsLocations implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_GOODS_LOCATIONS';

    private ColonyStorageRepositoryInterface $colonyStorageRepository;

    private ShipStorageRepositoryInterface $shipStorageRepository;

    private TradeOfferRepositoryInterface $tradeOfferRepository;

    private TradeStorageRepositoryInterface $tradeStorageRepository;

    private ShowGoodsLocationsRequestInterface $showGoodsLocationsRequest;

    public function __construct(
        ColonyStorageRepositoryInterface $colonyStorageRepository,
        ShipStorageRepositoryInterface $shipStorageRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository,
        ShowGoodsLocationsRequestInterface $showGoodsLocationsRequest
    ) {
        $this->colonyStorageRepository = $colonyStorageRepository;
        $this->shipStorageRepository = $shipStorageRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
        $this->showGoodsLocationsRequest = $showGoodsLocationsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $commodityId = $this->showGoodsLocationsRequest->getCommodityId();

        $game->setPageTitle(_('Lagerorte der Ware'));
        $game->setMacroInAjaxWindow('html/databasemacros.xhtml/commodityLocations');

        // set up colony locations array
        $colonyLocations = [];
        $colonyIterator = $this->colonyStorageRepository->getByUserAndCommodity($userId, $commodityId);
        foreach ($colonyIterator as $data) {
            $storageWrapper = new StorageWrapper($data['commodity_id'], $data['amount']);
            $storageWrapper->setEntityId($data['colonies_id']);
            $colonyLocations[] = $storageWrapper;
        }

        // set up ship locations array
        $shipLocations = [];
        $shipIterator = $this->shipStorageRepository->getByUserAndCommodity($userId, $commodityId);
        foreach ($shipIterator as $data) {
            $storageWrapper = new StorageWrapper($data['commodity_id'], $data['amount']);
            $storageWrapper->setEntityId($data['ships_id']);
            $shipLocations[] = $storageWrapper;
        }

        // set up trade post locations array
        $tradeStorageLocations = [];
        $tradeStorageIterator = $this->tradeStorageRepository->getByUserAndCommodity($userId, $commodityId);
        foreach ($tradeStorageIterator as $data) {
            $storageWrapper = new StorageWrapper($data->getGoodId(), $data->getAmount());
            $storageWrapper->setEntityId($data->getTradePostId());
            $tradeStorageLocations[] = $storageWrapper;
        }

        // set up trade offer locations array
        $tradeOfferLocations = [];
        $tradeOfferIterator = $this->tradeOfferRepository->getByUserAndCommodity($userId, $commodityId);
        foreach ($tradeOfferIterator as $data) {
            $storageWrapper = new StorageWrapper($data['commodity_id'], $data['amount']);
            $storageWrapper->setEntityId($data['posts_id']);
            $tradeOfferLocations[] = $storageWrapper;
        }

        $game->setTemplateVar('SHIP_LOCATIONS', $shipLocations);
        $game->setTemplateVar('COLONY_LOCATIONS', $colonyLocations);
        $game->setTemplateVar('POST_LOCATIONS', $tradeStorageLocations);
        $game->setTemplateVar('OFFER_LOCATIONS', $tradeOfferLocations);
    }
}
