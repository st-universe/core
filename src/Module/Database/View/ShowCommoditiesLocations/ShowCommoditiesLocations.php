<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowCommoditiesLocations;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Module\Database\Lib\StorageWrapper;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Repository\StorageRepositoryInterface;

/**
 * Shows the locations of a certain commodity
 */
final class ShowCommoditiesLocations implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COMMODITIES_LOCATIONS';

    public function __construct(
        private StorageRepositoryInterface $storageRepository,
        private ShowCommoditiesLocationsRequestInterface $showCommoditiesLocationsRequest,
        private DatabaseUiFactoryInterface $databaseUiFactory
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $commodityId = $this->showCommoditiesLocationsRequest->getCommodityId();

        $game->setPageTitle('Lagerorte der Ware');
        $game->setMacroInAjaxWindow('html/database/commodityLocations.twig');

        $game->setTemplateVar(
            'SPACECRAFT_LOCATIONS',
            array_map(
                fn (array $data): StorageWrapper => $this->databaseUiFactory->createStorageWrapper(
                    $data['commodity_id'],
                    $data['amount'],
                    $data['spacecraft_id']
                ),
                $this->storageRepository->getSpacecraftStorageByUserAndCommodity($user, $commodityId)
            )
        );
        $game->setTemplateVar(
            'COLONY_LOCATIONS',
            array_map(
                fn (array $data): StorageWrapper => $this->databaseUiFactory->createStorageWrapper(
                    $data['commodity_id'],
                    $data['amount'],
                    $data['colonies_id']
                ),
                $this->storageRepository->getColonyStorageByUserAndCommodity($user, $commodityId)
            )
        );
        $game->setTemplateVar(
            'POST_LOCATIONS',
            array_map(
                fn (Storage $storage): StorageWrapper => $this->databaseUiFactory->createStorageWrapper(
                    $storage->getCommodityId(),
                    $storage->getAmount(),
                    $storage->getTradePost()?->getId() ?? 0
                ),
                $this->storageRepository->getTradePostStorageByUserAndCommodity($user, $commodityId)
            )
        );
        $game->setTemplateVar(
            'OFFER_LOCATIONS',
            array_map(
                fn (array $data): StorageWrapper => $this->databaseUiFactory->createStorageWrapper(
                    $data['commodity_id'],
                    $data['amount'],
                    $data['posts_id']
                ),
                $this->storageRepository->getTradeOfferStorageByUserAndCommodity($user, $commodityId)
            )
        );
        $game->setTemplateVar(
            'TORPEDO_LOCATIONS',
            array_map(
                fn (array $data): StorageWrapper => $this->databaseUiFactory->createStorageWrapper(
                    $data['commodity_id'],
                    $data['amount'],
                    $data['spacecraft_id']
                ),
                $this->storageRepository->getTorpdeoStorageByUserAndCommodity($user, $commodityId)
            )
        );
    }
}
