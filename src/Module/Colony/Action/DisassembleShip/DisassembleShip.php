<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DisassembleShip;

use request;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\View\ShowShipDisassembly\ShowShipDisassembly;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class DisassembleShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DISASSEMBLE_SHIP';

    private ColonyLoaderInterface $colonyLoader;

    private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository;

    private ShipLoaderInterface $shipLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRemoverInterface $shipRemover;
    
    private ColonyStorageManagerInterface $colonyStorageManager;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        ShipLoaderInterface $shipLoader,
        ColonyRepositoryInterface $colonyRepository,
        ShipRemoverInterface $shipRemover,
        ColonyStorageManagerInterface $colonyStorageManager
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->shipLoader = $shipLoader;
        $this->colonyRepository = $colonyRepository;
        $this->shipRemover = $shipRemover;
        $this->colonyStorageManager = $colonyStorageManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipDisassembly::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($colony->getEps() < 20) {
            $game->addInformation(_('Zur Demontage des Schiffes wird 20 Energie benötigt'));
            return;
        }

        $colony->lowerEps(20);

        $this->colonyRepository->save($colony);

        $ship_id = request::getIntFatal('ship_id');

        $ship = $this->shipLoader->getByIdAndUser((int) $ship_id, $userId);
        $this->retrieveSomeIntactModules($ship, $colony, $game);

        //$ship->getSystems()->clear();

        $this->shipRemover->remove($ship);

        $game->addInformationf(_('Das Schiff wurde demontiert'));
    }

    private function retrieveSomeIntactModules($ship, $colony, $game): void
    {
        $intactModules = [];

        foreach($ship->getSystems() as $system)
        {
            if ($system->getModule() !== null
                && $system->getStatus() == 100)
            {
                $module = $system->getModule();

                if (!array_key_exists($module->getId(), $intactModules))
                {
                    $intactModules[$module->getId()] = $module;
                }
            }
        }

        $maxStorage = $colony->getMaxStorage();

        //retrieve 50% of all intact modules
        for ($i = 1; $i <= (int)(ceil(count($intactModules) / 2)); $i++)
        {
            if ($colony->getStorageSum() >= $maxStorage)
            {
                $game->addInformationf(_('Kein Lagerraum frei um Module zu recyclen!'));
                break;
            }
            
            $module = $intactModules[array_rand($intactModules)];

            $this->colonyStorageManager->upperStorage(
                $colony,
                $module->getCommodity(),
                1
            );

            $game->addInformationf(sprintf(_('Folgendes Modul konnte recycelt werden: %s'), $module->getName()));
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
