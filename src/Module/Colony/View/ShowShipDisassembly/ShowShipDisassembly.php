<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipDisassembly;

use Stu\Component\Colony\OrbitShipListRetrieverInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class ShowShipDisassembly implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_DISASSEMBLY';

    private ColonyLoaderInterface $colonyLoader;

    private ShowShipDisassemblyRequestInterface $showShipDisassemblyRequest;

    private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private OrbitShipListRetrieverInterface $orbitShipListRetriever;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowShipDisassemblyRequestInterface $showShipDisassemblyRequest,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        OrbitShipListRetrieverInterface $orbitShipListRetriever,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showShipDisassemblyRequest = $showShipDisassemblyRequest;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->orbitShipListRetriever = $orbitShipListRetriever;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showShipDisassemblyRequest->getColonyId(),
            $userId,
            false
        );

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            $this->showShipDisassemblyRequest->getFieldId(),
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
                    if ($ship->getUser()->getId() !== $userId) {
                        continue;
                    }
                    foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($ship->getRump()) as $rump_rel) {
                        if (array_key_exists($rump_rel->getBuildingFunction(), $fieldFunctions)) {
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
                _('Schiffsdemontage')
            );
            $game->setPagetitle(_('Schiffsdemontage'));
            $game->setTemplateFile('html/colony/component/shipDisassembly.twig');

            $game->setTemplateVar('SHIP_LIST', $repairableShips);
            $game->setTemplateVar('COLONY', $colony);
            $game->setTemplateVar('FIELD', $field);
        }
    }
}
