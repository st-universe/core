<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowField;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Entity\ColonyShipRepairInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ShowField implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_FIELD';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;

    private ShowFieldRequestInterface $showFieldRequest;

    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        ShowFieldRequestInterface $showFieldRequest,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->showFieldRequest = $showFieldRequest;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showFieldRequest->getColonyId(),
            $userId
        );

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            $this->showFieldRequest->getFieldId()
        );

        if ($field === null) {
            return;
        }

        $shipRepairProgress = $this->colonyShipRepairRepository->getByColonyField(
            $colony->getId(),
            $field->getFieldId()
        );

        $game->setPageTitle(sprintf('Feld %d - Informationen', $field->getFieldId()));
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/fieldaction');

        $game->setTemplateVar('FIELD', $field);
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('SHIP_BUILD_PROGRESS', $this->colonyShipQueueRepository->getByColony((int) $colony->getId()));
        $game->setTemplateVar('SHIP_REPAIR_PROGRESS', $shipRepairProgress);
        if ($field->hasBuilding()) {
            $game->setTemplateVar(
                'BUILDING_FUNCTION',
                $this->colonyLibFactory->createBuildingFunctionTal($field->getBuilding()->getFunctions()->toArray())
            );
        }
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
    }
}
