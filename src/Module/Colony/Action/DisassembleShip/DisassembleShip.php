<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DisassembleShip;

use request;
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

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        ShipLoaderInterface $shipLoader,
        ColonyRepositoryInterface $colonyRepository,
        ShipRemoverInterface $shipRemover
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->shipLoader = $shipLoader;
        $this->colonyRepository = $colonyRepository;
        $this->shipRemover = $shipRemover;
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

        //TODO revive some modules?
        //$this->moduleRepository->find((int) $key);
        //with key = ShipSystem->module_id
        // from Module to Commidity

        $ship = $this->shipLoader->getByIdAndUser((int) $ship_id, $userId);
        $this->shipRemover->remove($ship);

        $game->addInformationf(_('Das Schiff wurde demontiert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
