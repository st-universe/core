<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\FirstColony;

use InvalidArgumentException;
use Override;
use RuntimeException;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\PlanetColonizationInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class FirstColony implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FIRST_COLONY';

    public function __construct(private FirstColonyRequestInterface $firstColonyRequest, private BuildingRepositoryInterface $buildingRepository, private PlanetColonizationInterface $planetColonization, private ColonyRepositoryInterface $colonyRepository, private StorageManagerInterface $storageManager, private CommodityRepositoryInterface $commodityRepository, private UserRepositoryInterface $userRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user->getState() !== UserEnum::USER_STATE_UNCOLONIZED) {
            $game->addInformation(_('Es ist bereits eine Kolonie kolonisiert'));
            return;
        }

        $planetId = $this->firstColonyRequest->getPlanetId();

        $colony = $this->colonyRepository->find($planetId);

        if ($colony === null || !$colony->isFree()) {
            $game->addInformation(_('Dieser Planet wurde bereits besiedelt'));
            return;
        }
        $colonyList = $this->colonyRepository->getStartingByFaction($user->getFactionId());

        if (!array_key_exists($planetId, $colonyList)) {
            return;
        }

        $faction = $user->getFaction();

        $startingBuilding =  $this->buildingRepository->find($faction->getStartBuildingId());
        if ($startingBuilding === null) {
            throw new RuntimeException(sprintf('buildingId %d not found', $faction->getStartBuildingId()));
        }
        $this->planetColonization->colonize(
            $colony,
            $user->getId(),
            $startingBuilding
        );

        $this->storageManager->upperStorage(
            $colony,
            $this->getCommodity(CommodityTypeEnum::COMMODITY_BUILDING_MATERIALS),
            150
        );
        $this->storageManager->upperStorage(
            $colony,
            $this->getCommodity(CommodityTypeEnum::COMMODITY_TRANSPARENT_ALUMINIUM),
            150
        );
        $this->storageManager->upperStorage(
            $colony,
            $this->getCommodity(CommodityTypeEnum::COMMODITY_DURANIUM),
            150
        );
        $this->storageManager->upperStorage(
            $colony,
            $this->getCommodity(CommodityTypeEnum::COMMODITY_DEUTERIUM),
            100
        );

        $user->setState(UserEnum::USER_STATE_ACTIVE);

        $this->userRepository->save($user);

        // Database entries for colonyclass
        $game->checkDatabaseItem($colony->getColonyClass()->getDatabaseId());

        $game->redirectTo('./colony.php?id=' . $colony->getId());
    }

    private function getCommodity(int $commodityId): Commodity
    {
        $commodity = $this->commodityRepository->find($commodityId);
        if ($commodity === null) {
            throw new InvalidArgumentException(sprintf('commodityId %d does not exist', $commodityId));
        }

        return $commodity;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
