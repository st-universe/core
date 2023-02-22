<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\LandShip;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipTorpedoManagerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

final class LandShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LAND_SHIP';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRemoverInterface $shipRemover;

    private ShipLoaderInterface $shipLoader;

    private ShipTorpedoManagerInterface $shipTorpedoManager;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        ShipRemoverInterface $shipRemover,
        ShipLoaderInterface $shipLoader,
        ShipTorpedoManagerInterface $shipTorpedoManager,
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipCrewRepositoryInterface $shipCrewRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->shipRemover = $shipRemover;
        $this->shipLoader = $shipLoader;
        $this->shipTorpedoManager = $shipTorpedoManager;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
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
        if (!$colony->storagePlaceLeft()) {
            $game->addInformation(_('Kein Lagerraum verfügbar'));
            return;
        }

        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $colony,
            $this->colonyLibFactory->createColonyCommodityProduction($colony)->getProduction()
        )->getFreeAssignmentCount();

        if ($ship->getCrewCount() > $freeAssignmentCount) {
            $game->addInformation(_('Nicht genügend Platz für die Crew auf der Kolonie'));
            return;
        }

        $this->colonyStorageManager->upperStorage($colony, $ship->getRump()->getCommodity(), 1);

        foreach ($ship->getStorage() as $stor) {
            $count = (int) min($stor->getAmount(), $colony->getMaxStorage() - $colony->getStorageSum());
            if ($count > 0) {
                $this->colonyStorageManager->upperStorage($colony, $stor->getCommodity(), $count);
            }
        }

        $this->colonyRepository->save($colony);

        $this->retrieveLoadedTorpedos($wrapper, $colony, $game);

        $this->transferCrewToColony($ship, $colony);

        $this->shipRemover->remove($ship);

        $game->addInformationf(_('Die %s ist gelandet'), $ship->getName());
    }

    private function transferCrewToColony(ShipInterface $ship, ColonyInterface $colony): void
    {
        foreach ($ship->getCrewlist() as $crewAssignment) {
            $crewAssignment->setColony($colony);
            $crewAssignment->setShip(null);
            $crewAssignment->setSlot(null);
            $colony->getCrewAssignments()->add($crewAssignment);
            $this->shipCrewRepository->save($crewAssignment);
        }

        $ship->getCrewlist()->clear();
    }

    private function retrieveLoadedTorpedos(ShipWrapperInterface $wrapper, $colony, $game): void
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
        $this->colonyStorageManager->upperStorage(
            $colony,
            $commodity,
            $amount
        );

        $this->shipTorpedoManager->removeTorpedo($wrapper);

        $game->addInformationf(sprintf(_('%d Einheiten folgender Ware konnten recycelt werden: %s'), $amount, $commodity->getName()));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
