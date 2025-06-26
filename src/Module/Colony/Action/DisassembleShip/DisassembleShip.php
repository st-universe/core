<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DisassembleShip;

use Override;
use request;
use RuntimeException;
use Stu\Component\Spacecraft\Module\ModuleRecyclingInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ClearTorpedoInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class DisassembleShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DISASSEMBLE_SHIP';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ShipLoaderInterface $shipLoader,
        private ColonyRepositoryInterface $colonyRepository,
        private SpacecraftRemoverInterface $spacecraftRemover,
        private StorageManagerInterface $storageManager,
        private CommodityRepositoryInterface $commodityRepository,
        private ClearTorpedoInterface $clearTorpedo,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private ModuleRecyclingInterface $moduleRecycling
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $changeable = $colony->getChangeable();

        if ($changeable->getEps() < 20) {
            $game->addInformation('Zur Demontage des Schiffes wird 20 Energie benötigt');
            return;
        }

        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $colony
        )->getFreeAssignmentCount();

        $ship_id = request::getIntFatal('ship_id');
        $wrapper = $this->shipLoader->getWrapperByIdAndUser($ship_id, $userId);

        $ship = $wrapper->get();
        if ($ship->getCrewCount() > $freeAssignmentCount) {
            $game->addInformation('Nicht genügend Platz für die Crew auf der Kolonie');
            return;
        }

        $changeable->lowerEps(20);

        $this->colonyRepository->save($colony);

        $this->moduleRecycling->retrieveSomeModules($ship, $colony, $game);
        $this->retrieveReactorLoad($wrapper, $colony, $game);
        $this->retrieveLoadedTorpedos($wrapper, $colony, $game);

        $this->transferCrewToColony($ship, $colony);

        $this->spacecraftRemover->remove($ship);

        $game->addInformation('Das Schiff wurde demontiert');
    }

    private function transferCrewToColony(Ship $ship, Colony $colony): void
    {
        foreach ($ship->getCrewAssignments() as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $colony);
        }
    }

    private function retrieveReactorLoad(ShipWrapperInterface $wrapper, Colony $colony, GameControllerInterface $game): void
    {
        $reactorWrapper = $wrapper->getReactorWrapper();
        if ($reactorWrapper === null) {
            return;
        }

        $reactor = $reactorWrapper->get();

        $loads = (int) floor($reactorWrapper->getLoad() / $reactor->getLoadUnits());
        if ($loads < 1) {
            return;
        }

        $maxStorage = $colony->getMaxStorage();

        foreach ($reactor->getLoadCost() as $commodityId => $loadCost) {
            if ($colony->getStorageSum() >= $maxStorage) {
                $game->addInformationf(
                    'Kein Lagerraum frei um %s-Mix zu sichern!',
                    $reactor->getSystemType()->getDescription()
                );
                break;
            }

            $amount = $loads * $loadCost;
            if ($maxStorage - $colony->getStorageSum() < $amount) {
                $amount = $maxStorage - $colony->getStorageSum();
            }

            $commodity = $this->commodityRepository->find($commodityId);
            if ($commodity === null) {
                throw new RuntimeException(sprintf('commodityId %d does not exist', $commodityId));
            }
            $this->storageManager->upperStorage(
                $colony,
                $commodity,
                $amount
            );

            $game->addInformationf('%d Einheiten folgender Ware konnten recycelt werden: %s', $amount, $commodity->getName());
        }
    }

    private function retrieveLoadedTorpedos(ShipWrapperInterface $wrapper, Colony $colony, GameControllerInterface $game): void
    {
        $ship = $wrapper->get();
        $torpedoStorage = $ship->getTorpedoStorage();

        if ($torpedoStorage === null) {
            return;
        }

        $maxStorage = $colony->getMaxStorage();

        if ($colony->getStorageSum() >= $maxStorage) {
            $game->addInformation('Kein Lagerraum frei um geladene Torpedos zu sichern!');
            return;
        }

        $amount = $torpedoStorage->getStorage()->getAmount();
        if ($maxStorage - $colony->getStorageSum() < $amount) {
            $amount = $maxStorage - $colony->getStorageSum();
        }

        $commodity = $torpedoStorage->getStorage()->getCommodity();
        $this->storageManager->upperStorage(
            $colony,
            $commodity,
            $amount
        );

        $this->clearTorpedo->clearTorpedoStorage($wrapper);

        $game->addInformationf('%d Einheiten folgender Ware konnten recycelt werden: %s', $amount, $commodity->getName());
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
