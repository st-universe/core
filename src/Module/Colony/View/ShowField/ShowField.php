<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowField;

use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalStatusBar;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyShipRepairInterface;
use Stu\Orm\Repository\BuildingUpgradeRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\TerraformingRepositoryInterface;

final class ShowField implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_FIELD';

    private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;

    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private TerraformingRepositoryInterface $terraformingRepository;

    private BuildingUpgradeRepositoryInterface $buildingUpgradeRepository;

    private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository;

    public function __construct(
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        TerraformingRepositoryInterface $terraformingRepository,
        BuildingUpgradeRepositoryInterface $buildingUpgradeRepository,
        ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->terraformingRepository = $terraformingRepository;
        $this->buildingUpgradeRepository = $buildingUpgradeRepository;
        $this->colonyTerraformingRepository = $colonyTerraformingRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser());
        $host = $field->getHost();

        $terraformingOptions = $this->terraformingRepository->getBySourceFieldTypeAndUser(
            $field->getFieldType(),
            $userId
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

        if ($host instanceof ColonyInterface) {
            $game->setTemplateVar('COLONY', $host);
            $game->setTemplateVar('FORM_ACTION', 'colony.php');
            $game->setTemplateVar('SHIP_BUILD_PROGRESS', $this->colonyShipQueueRepository->getByColony($host->getId()));

            $shipRepairProgress = array_map(
                fn (ColonyShipRepairInterface $repair): ShipWrapperInterface => $this->shipWrapperFactory->wrapShip($repair->getShip()),
                $this->colonyShipRepairRepository->getByColonyField(
                    $host->getId(),
                    $field->getFieldId()
                )
            );
            $game->setTemplateVar('SHIP_REPAIR_PROGRESS', $shipRepairProgress);

            $terraFormingState = null;
            $terraFormingState = $this->colonyTerraformingRepository->getByColonyAndField(
                $host->getId(),
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

            $game->setTemplateVar(
                'TERRAFORMING_BAR',
                $terraFormingBar
            );
            $game->setTemplateVar(
                'TERRAFORMING_STATE',
                $terraFormingState
            );
        } else {
            $game->setTemplateVar('FORM_ACTION', '/admin/index.php');
        }


        $game->setPageTitle(sprintf('Feld %d - Informationen', $field->getFieldId()));
        $game->setMacroInAjaxWindow('html/colony/component/fieldAction.twig');

        $game->setTemplateVar('FIELD', $field);
        $game->setTemplateVar('HOST', $host);

        if ($field->hasBuilding()) {
            $game->setTemplateVar(
                'BUILDING_FUNCTION',
                $this->colonyLibFactory->createBuildingFunctionTal($field->getBuilding()->getFunctions()->toArray())
            );
        }
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
    }
}
