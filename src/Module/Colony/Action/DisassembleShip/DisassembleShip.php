<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DisassembleShip;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Torpedo\ClearTorpedoInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class DisassembleShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DISASSEMBLE_SHIP';

    private ColonyLoaderInterface $colonyLoader;

    private ShipLoaderInterface $shipLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRemoverInterface $shipRemover;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private CommodityRepositoryInterface $commodityRepository;

    private ClearTorpedoInterface $clearTorpedo;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShipLoaderInterface $shipLoader,
        ColonyRepositoryInterface $colonyRepository,
        ShipRemoverInterface $shipRemover,
        ColonyStorageManagerInterface $colonyStorageManager,
        CommodityRepositoryInterface $commodityRepository,
        ClearTorpedoInterface $clearTorpedo,
        ColonyLibFactoryInterface $colonyLibFactory,
        TroopTransferUtilityInterface $troopTransferUtility
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipLoader = $shipLoader;
        $this->colonyRepository = $colonyRepository;
        $this->shipRemover = $shipRemover;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->commodityRepository = $commodityRepository;
        $this->clearTorpedo = $clearTorpedo;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($colony->getEps() < 20) {
            $game->addInformation(_('Zur Demontage des Schiffes wird 20 Energie benötigt'));
            return;
        }

        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $colony
        )->getFreeAssignmentCount();

        $ship_id = request::getIntFatal('ship_id');
        $wrapper = $this->shipLoader->getWrapperByIdAndUser($ship_id, $userId);
        $ship = $wrapper->get();
        if ($ship->getCrewCount() > $freeAssignmentCount) {
            $game->addInformation(_('Nicht genügend Platz für die Crew auf der Kolonie'));
            return;
        }

        $colony->lowerEps(20);

        $this->colonyRepository->save($colony);

        $this->retrieveSomeIntactModules($ship, $colony, $game);
        $this->retrieveReactorLoad($wrapper, $colony, $game);
        $this->retrieveLoadedTorpedos($wrapper, $colony, $game);

        $this->transferCrewToColony($ship, $colony);

        $this->shipRemover->remove($ship);

        $game->addInformationf(_('Das Schiff wurde demontiert'));
    }

    private function transferCrewToColony(ShipInterface $ship, ColonyInterface $colony): void
    {
        foreach ($ship->getCrewAssignments() as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $colony);
        }
    }

    private function retrieveSomeIntactModules(ShipInterface $ship, ColonyInterface $colony, GameControllerInterface $game): void
    {
        $intactModules = [];

        foreach ($ship->getSystems() as $system) {
            if (
                $system->getModule() !== null
                && $system->getStatus() == 100
            ) {
                $module = $system->getModule();

                if (!array_key_exists($module->getId(), $intactModules)) {
                    $intactModules[$module->getId()] = $module;
                }
            }
        }

        $maxStorage = $colony->getMaxStorage();

        //retrieve 50% of all intact modules
        $recycleCount = (int) ceil(count($intactModules) / 2);
        for ($i = 1; $i <= $recycleCount; $i++) {
            if ($colony->getStorageSum() >= $maxStorage) {
                $game->addInformationf(_('Kein Lagerraum frei um Module zu recyclen!'));
                break;
            }

            $module = $intactModules[array_rand($intactModules)];
            unset($intactModules[$module->getId()]);

            $this->colonyStorageManager->upperStorage(
                $colony,
                $module->getCommodity(),
                1
            );

            $game->addInformationf(sprintf(_('Folgendes Modul konnte recycelt werden: %s'), $module->getName()));
        }
    }

    private function retrieveReactorLoad(ShipWrapperInterface $wrapper, ColonyInterface $colony, GameControllerInterface $game): void
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
                    _('Kein Lagerraum frei um %s-Mix zu sichern!'),
                    $reactor->getSystemType()->getDescription()
                );
                break;
            }

            $amount = $loads * $loadCost;
            if ($maxStorage - $colony->getStorageSum() < $amount) {
                $amount = $maxStorage - $colony->getStorageSum();
            }

            $commodity = $this->commodityRepository->find($commodityId);
            $this->colonyStorageManager->upperStorage(
                $colony,
                $commodity,
                $amount
            );

            $game->addInformationf(sprintf(_('%d Einheiten folgender Ware konnten recycelt werden: %s'), $amount, $commodity->getName()));
        }
    }

    private function retrieveLoadedTorpedos(ShipWrapperInterface $wrapper, ColonyInterface $colony, GameControllerInterface $game): void
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

        $this->clearTorpedo->clearTorpedoStorage($wrapper);

        $game->addInformationf(sprintf(_('%d Einheiten folgender Ware konnten recycelt werden: %s'), $amount, $commodity->getName()));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
