<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowCommoditiesLocations;

use Stu\Lib\StorageWrapper\StorageWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class ShowCommoditiesLocations implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COMMODITIES_LOCATIONS';

    private StorageRepositoryInterface $storageRepository;

    private ShowCommoditiesLocationsRequestInterface $showCommoditiesLocationsRequest;

    public function __construct(
        StorageRepositoryInterface $storageRepository,
        ShowCommoditiesLocationsRequestInterface $showCommoditiesLocationsRequest
    ) {
        $this->storageRepository = $storageRepository;
        $this->showCommoditiesLocationsRequest = $showCommoditiesLocationsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $commodityId = $this->showCommoditiesLocationsRequest->getCommodityId();

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

        // set up torpedo storage locations array
        $torpedoStorageLocations = [];
        $torpedoStorageIterator = $this->storageRepository->getTorpdeoStorageByUserAndCommodity($userId, $commodityId);
        foreach ($torpedoStorageIterator as $data) {
            $storageWrapper = new StorageWrapper($data['commodity_id'], $data['amount']);
            $storageWrapper->setEntityId($data['ship_id']);
            $torpedoStorageLocations[] = $storageWrapper;
        }

        $game->setTemplateVar('SHIP_LOCATIONS', $shipLocations);
        $game->setTemplateVar('COLONY_LOCATIONS', $colonyLocations);
        $game->setTemplateVar('POST_LOCATIONS', $tradeStorageLocations);
        $game->setTemplateVar('OFFER_LOCATIONS', $tradeOfferLocations);
        $game->setTemplateVar('TORPEDO_LOCATIONS', $torpedoStorageLocations);
    }
}
