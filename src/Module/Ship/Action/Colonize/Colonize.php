<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Colonize;

use InvalidArgumentException;
use Override;
use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Player\ColonizationCheckerInterface;
use Stu\Module\Colony\Lib\PlanetColonizationInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyDepositMiningRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRumpColonizationBuildingRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Colonize implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_COLONIZE';

    public function __construct(
        private ShipRumpColonizationBuildingRepositoryInterface $shipRumpColonizationBuildingRepository,
        private BuildingRepositoryInterface $buildingRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private ColonyRepositoryInterface $colonyRepository,
        private CommodityRepositoryInterface $commodityRepository,
        private ColonyDepositMiningRepositoryInterface $colonyDepositMiningRepository,
        private UserRepositoryInterface $userRepository,
        private ShipLoaderInterface $shipLoader,
        private PlanetColonizationInterface $planetColonization,
        private StorageManagerInterface $storageManager,
        private SpacecraftRemoverInterface $spacecraftRemover,
        private InteractionCheckerInterface $interactionChecker,
        private ColonizationCheckerInterface $colonizationChecker,
        private TroopTransferUtilityInterface $troopTransferUtility
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $colonyId = request::getIntFatal('colid');
        $fieldId = request::getIntFatal('field');

        $colony = $this->colonyRepository->find($colonyId);
        $field = $this->planetFieldRepository->find($fieldId);

        if ($field === null || $colony === null) {
            return;
        }

        if (!$ship->getRump()->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE)) {
            return;
        }

        if ($colony !== $field->getHost()) {
            return;
        }
        if (!$this->interactionChecker->checkPosition($colony, $ship)) {
            return;
        }
        if ($this->colonizationChecker->canColonize($user, $colony) === false) {
            return;
        }

        $shipRumpColonizationBuilding = $this->shipRumpColonizationBuildingRepository->findByShipRump($ship->getRump());
        if ($shipRumpColonizationBuilding === null) {
            return;
        }

        $building = $this->buildingRepository->find($shipRumpColonizationBuilding->getBuildingId());
        if ($building === null) {
            return;
        }

        if (!$user->hasColony()) {
            $user->setState(UserEnum::USER_STATE_ACTIVE);
            $this->userRepository->save($user);
            $this->planetColonization->colonize(
                $colony,
                $user,
                $building,
                $field
            );
            $this->storageManager->upperStorage(
                $colony,
                $this->getCommodity(CommodityTypeConstants::COMMODITY_BUILDING_MATERIALS),
                150
            );
            $this->storageManager->upperStorage(
                $colony,
                $this->getCommodity(CommodityTypeConstants::COMMODITY_TRANSPARENT_ALUMINIUM),
                150
            );
            $this->storageManager->upperStorage(
                $colony,
                $this->getCommodity(CommodityTypeConstants::COMMODITY_DURANIUM),
                150
            );
            $this->storageManager->upperStorage(
                $colony,
                $this->getCommodity(CommodityTypeConstants::COMMODITY_DEUTERIUM),
                100
            );
        } else {

            $this->planetColonization->colonize(
                $colony,
                $user,
                $building,
                $field
            );
        }


        $this->transferCrewToColony($ship, $colony);

        $this->createUserDepositMinings($colony);

        $this->spacecraftRemover->remove($ship);

        $game->checkDatabaseItem($colony->getColonyClass()->getDatabaseId());

        $game->redirectTo(sprintf(
            '/colony.php?%s=1&id=%d',
            ShowColony::VIEW_IDENTIFIER,
            $colony->getId()
        ));
    }

    private function getCommodity(int $commodityId): Commodity
    {
        $commodity = $this->commodityRepository->find($commodityId);
        if ($commodity === null) {
            throw new InvalidArgumentException(sprintf('commodityId %d does not exist', $commodityId));
        }

        return $commodity;
    }

    private function transferCrewToColony(Ship $ship, Colony $colony): void
    {
        foreach ($ship->getCrewAssignments() as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $colony);
        }
    }

    private function createUserDepositMinings(Colony $colony): void
    {
        $deposits = $colony->getColonyClass()->getColonyClassDeposits();
        $userMinings = $this->colonyDepositMiningRepository->getCurrentUserDepositMinings($colony);

        foreach ($deposits as $deposit) {
            //check if user already mined this commodity on this colony
            if (array_key_exists($deposit->getCommodity()->getId(), $userMinings)) {
                continue;
            }

            //create new mining entry
            $depositMining = $this->colonyDepositMiningRepository->prototype();
            $depositMining->setUser($colony->getUser());
            $depositMining->setColony($colony);
            $depositMining->setCommodity($deposit->getCommodity());
            $depositMining->setAmountLeft(random_int($deposit->getMinAmount(), $deposit->getMaxAmount()));

            $this->colonyDepositMiningRepository->save($depositMining);
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
