<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowField;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalStatusBar;
use Stu\Orm\Entity\ColonyShipRepairInterface;
use Stu\Orm\Repository\BuildingUpgradeRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\TerraformingRepositoryInterface;

final class ShowField implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_FIELD';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;

    private ShowFieldRequestInterface $showFieldRequest;

    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private TerraformingRepositoryInterface $terraformingRepository;

    private BuildingUpgradeRepositoryInterface $buildingUpgradeRepository;

    private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        ShowFieldRequestInterface $showFieldRequest,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        TerraformingRepositoryInterface $terraformingRepository,
        BuildingUpgradeRepositoryInterface $buildingUpgradeRepository,
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->showFieldRequest = $showFieldRequest;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->terraformingRepository = $terraformingRepository;
        $this->buildingUpgradeRepository = $buildingUpgradeRepository;
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showFieldRequest->getColonyId(),
            $userId,
            false
        );

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            $this->showFieldRequest->getFieldId()
        );

        if ($field === null) {
            return;
        }

        $terraformingOptions = $this->terraformingRepository->getBySourceFieldTypeAndUser(
            $field->getFieldType(),
            $userId
        );

        $shipRepairProgress = array_map(
            fn (ColonyShipRepairInterface $repair): ShipWrapperInterface => $this->shipWrapperFactory->wrapShip($repair->getShip()),
            $this->colonyShipRepairRepository->getByColonyField(
                $colony->getId(),
                $field->getFieldId()
            )
        );

        if (
            !$field->isUnderConstruction()
            && $field->getBuilding() !== null
        ) {
            $upgradeOptions = $this
                ->buildingUpgradeRepository
                ->getByBuilding((int) $field->getBuildingId(), $userId);
        } else {
            $upgradeOptions = [];
        }

        $terraFormingState = $this->colonyTerraformingRepository->getByColonyAndField(
            $field->getColonyId(),
            $field->getId()
        );

        $terraFormingBar = null;
        if ($terraFormingState !== null) {
            $terraFormingBar = (new TalStatusBar())
                ->setColor(StatusBarColorEnum::STATUSBAR_GREEN)
                ->setLabel('Fortschritt')
                ->setMaxValue($terraFormingState->getTerraforming()->getDuration())
                ->setValue($terraFormingState->getProgress())
                ->render();
        }

        $game->setPageTitle(sprintf('Feld %d - Informationen', $field->getFieldId()));
        $game->setMacroInAjaxWindow('html/colonymacros.xhtml/fieldaction');

        $game->setTemplateVar('FIELD', $field);
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('SHIP_BUILD_PROGRESS', $this->colonyShipQueueRepository->getByColony($colony->getId()));
        $game->setTemplateVar('SHIP_REPAIR_PROGRESS', $shipRepairProgress);
        if ($field->hasBuilding()) {
            $game->setTemplateVar(
                'BUILDING_FUNCTION',
                $this->colonyLibFactory->createBuildingFunctionTal($field->getBuilding()->getFunctions()->toArray())
            );
        }
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
        $game->setTemplateVar(
            'HAS_UPGRADE_OR_TERRAFORMING_OPTION',
            (!$field->isUnderConstruction()
                && $upgradeOptions !== []
            ) || ($terraformingOptions !== []
                && !$field->hasBuilding()
            )
        );
        $game->setTemplateVar(
            'TERRAFORMING_OPTIONS',
            $terraformingOptions
        );
        $game->setTemplateVar(
            'UPGRADE_OPTIONS',
            $upgradeOptions
        );
        $game->setTemplateVar(
            'TERRAFORMING_BAR',
            $terraFormingBar
        );
        $game->setTemplateVar(
            'TERRAFORMING_STATE',
            $terraFormingState
        );
    }
}
