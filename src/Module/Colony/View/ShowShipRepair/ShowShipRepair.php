<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipRepair;

use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class ShowShipRepair implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_REPAIR';

    private ColonyLoaderInterface $colonyLoader;

    private ShowShipRepairRequestInterface $showShipRepairRequest;

    private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private OrbitShipListRetrieverInterface $orbitShipListRetriever;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowShipRepairRequestInterface $showShipRepairRequest,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        OrbitShipListRetrieverInterface $orbitShipListRetriever,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showShipRepairRequest = $showShipRepairRequest;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->orbitShipListRetriever = $orbitShipListRetriever;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showShipRepairRequest->getColonyId(),
            $userId,
            false
        );

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            $this->showShipRepairRequest->getFieldId()
        );

        if ($field === null) {
            return;
        }

        $fieldFunctions = $field->getBuilding()->getFunctions()->toArray();

        $colonySurface = $this->colonyLibFactory->createColonySurface($colony);

        if ($colonySurface->hasShipyard()) {
            $repairableShips = [];
            foreach ($this->orbitShipListRetriever->retrieve($colony) as $fleet) {
                /** @var ShipInterface $ship */
                foreach ($fleet['ships'] as $ship) {
                    $wrapper = $this->shipWrapperFactory->wrapShip($ship);

                    if (
                        !$wrapper->canBeRepaired() || $ship->isUnderRepair()
                    ) {
                        continue;
                    }
                    foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($ship->getRump()) as $rump_rel) {
                        if (array_key_exists($rump_rel->getBuildingFunction(), $fieldFunctions)) {
                            $repairableShips[$ship->getId()] = $wrapper;
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
                sprintf(
                    '?%s=1&id=%d',
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
            $game->setTemplateFile('html/colony/component/shipRepair.twig');

            $game->setTemplateVar('REPAIRABLE_SHIP_LIST', $repairableShips);
            $game->setTemplateVar('COLONY', $colony);
            $game->setTemplateVar('FIELD', $field);
        }
    }
}
