<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipRepair;

use Ship;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class ShowShipRepair implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_REPAIR';

    private $colonyLoader;

    private $showShipRepairRequest;

    private $shipRumpBuildingFunctionRepository;

    private $planetFieldRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowShipRepairRequestInterface $showShipRepairRequest,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showShipRepairRequest = $showShipRepairRequest;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showShipRepairRequest->getColonyId(),
            $userId
        );

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            $this->showShipRepairRequest->getFieldId()
        );

        if ($field === null) {
            return;
        }

        if ($colony->hasShipyard()) {

            $repairableShips = [];
            foreach ($colony->getOrbitShipList($userId) as $fleet) {
                /** @var Ship $ship */
                foreach ($fleet['ships'] as $ship_id => $ship) {
                    if (!$ship->canBeRepaired() || $ship->getState() == SHIP_STATE_REPAIR) {
                        continue;
                    }
                    foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump((int) $ship->getRumpId()) as $rump_rel) {
                        if (array_key_exists($rump_rel->getBuildingFunction(), $field->getBuilding()->getFunctions())) {
                            $repairableShips[$ship->getId()] = $ship;
                            break;
                        }
                    }
                }
            }

            $game->appendNavigationPart(
                'colony.php',
                _('Kolonien')
            );
            $game->appendNavigationPart(
                sprintf('?%s=1&id=%d',
                    ShowColony::VIEW_IDENTIFIER,
                    $colony->getId()
                ),
                $colony->getName()
            );
            $game->appendNavigationPart(
                sprintf(
                    '?id=%s&%d=1&fid=%d',
                    $colony->getId(),
                    static::VIEW_IDENTIFIER,
                    $field->getFieldId()
                ),
                _('Schiffreparatur')
            );
            $game->setPagetitle(_('Schiffreparatur'));
            $game->setTemplateFile('html/colony_shiprepair.xhtml');

            $game->setTemplateVar('REPAIRABLE_SHIP_LIST', $repairableShips);
            $game->setTemplateVar('COLONY', $colony);
            $game->setTemplateVar('FIELD', $field);
        }
    }
}
