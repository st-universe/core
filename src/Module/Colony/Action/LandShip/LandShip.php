<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\LandShip;

use Override;
use request;
use RuntimeException;
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

final class LandShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_LAND_SHIP';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private StorageManagerInterface $storageManager, private ColonyRepositoryInterface $colonyRepository, private SpacecraftRemoverInterface $spacecraftRemover, private ShipLoaderInterface $shipLoader, private ClearTorpedoInterface $clearTorpedo, private ColonyLibFactoryInterface $colonyLibFactory, private TroopTransferUtilityInterface $troopTransferUtility) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $wrapper = $this->shipLoader->find(request::getIntFatal('shipid'));

        if ($wrapper === null) {
            return;
        }

        $ship = $wrapper->get();

        if (
            $ship->getUser()->getId() !== $game->getUser()->getId()
            || !$wrapper->canLandOnCurrentColony()
        ) {
            return;
        }
        if ($colony->getMaxStorage() <= $colony->getStorageSum()) {
            $game->addInformation(_('Kein Lagerraum verfügbar'));
            return;
        }

        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $colony
        )->getFreeAssignmentCount();

        if ($ship->getCrewCount() > $freeAssignmentCount) {
            $game->addInformation(_('Nicht genügend Platz für die Crew auf der Kolonie'));
            return;
        }

        $commodity = $ship->getRump()->getCommodity();
        if ($commodity === null) {
            throw new RuntimeException(sprintf('rumpId %d does not have commodity', $ship->getRumpId()));
        }

        $this->storageManager->upperStorage($colony, $commodity, 1);

        foreach ($ship->getStorage() as $stor) {
            $count = min($stor->getAmount(), $colony->getMaxStorage() - $colony->getStorageSum());
            if ($count > 0) {
                $this->storageManager->upperStorage($colony, $stor->getCommodity(), $count);
            }
        }

        $this->colonyRepository->save($colony);

        $this->retrieveLoadedTorpedos($wrapper, $colony, $game);

        $this->transferCrewToColony($ship, $colony);

        $this->spacecraftRemover->remove($ship);

        $game->addInformationf(_('Die %s ist gelandet'), $ship->getName());
    }

    private function transferCrewToColony(Ship $ship, Colony $colony): void
    {
        foreach ($ship->getCrewAssignments() as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $colony);
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
            $game->addInformationf(_('Kein Lagerraum frei um geladene Torpedos zu sichern!'));
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

        $game->addInformationf(sprintf(_('%d Einheiten folgender Ware konnten recycelt werden: %s'), $amount, $commodity->getName()));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
