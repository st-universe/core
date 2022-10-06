<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowGoodsLocations;

use Stu\Lib\StorageWrapper\StorageWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class ShowGoodsLocations implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_GOODS_LOCATIONS';

    private StorageRepositoryInterface $storageRepository;

    private ShowGoodsLocationsRequestInterface $showGoodsLocationsRequest;

    public function __construct(
        StorageRepositoryInterface $storageRepository,
        ShowGoodsLocationsRequestInterface $showGoodsLocationsRequest
    ) {
        $this->storageRepository = $storageRepository;
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
        $colonyIterator = $this->storageRepository->getColonyStorageByUserAndCommodity($userId, $commodityId);
        foreach ($colonyIterator as $data) {
            $storageWrapper = new StorageWrapper($data['commodity_id'], $data['amount']);
            $storageWrapper->setEntityId($data['colonies_id']);
            $colonyLocations[] = $storageWrapper;
        }

        // set up ship locations array
        $shipLocations = [];
        $shipIterator = $this->storageRepository->getShipStorageByUserAndCommodity($userId, $commodityId);
        foreach ($shipIterator as $data) {
            $storageWrapper = new StorageWrapper($data['commodity_id'], $data['amount']);
            $storageWrapper->setEntityId($data['ships_id']);
            $shipLocations[] = $storageWrapper;
        }

        // set up trade post locations array
        $tradeStorageLocations = [];
        $tradeStorages = $this->storageRepository->getTradePostStorageByUserAndCommodity($userId, $commodityId);
        foreach ($tradeStorages as $storage) {
            $storageWrapper = new StorageWrapper($storage->getCommodityId(), $storage->getAmount());
            $storageWrapper->setEntityId($storage->getTradePost()->getId());
            $tradeStorageLocations[] = $storageWrapper;
        }

        // set up trade offer locations array
        $tradeOfferLocations = [];
        $tradeOfferIterator = $this->storageRepository->getTradeOfferStorageByUserAndCommodity($userId, $commodityId);
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
