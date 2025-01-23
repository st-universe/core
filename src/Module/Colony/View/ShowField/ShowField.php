<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowField;

use Override;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonyShipRepairInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\BuildingUpgradeRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\ColonyTerraformingRepositoryInterface;
use Stu\Orm\Repository\TerraformingRepositoryInterface;

final class ShowField implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_FIELD';

    public function __construct(
        private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private PlanetFieldHostProviderInterface $planetFieldHostProvider,
        private TerraformingRepositoryInterface $terraformingRepository,
        private BuildingUpgradeRepositoryInterface $buildingUpgradeRepository,
        private ColonyTerraformingRepositoryInterface $colonyTerraformingRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private StatusBarFactoryInterface $statusBarFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser(), false);
        $host = $field->getHost();
        $building = $field->getBuilding();

        $terraformingOptions = $this->terraformingRepository->getBySourceFieldTypeAndUser(
            $field->getFieldType(),
            $userId
        );


        if (
            !$field->isUnderConstruction()
            && $building !== null
        ) {
            $upgradeOptions = $this
                ->buildingUpgradeRepository
                ->getByBuilding($building->getId(), $userId);
        } else {
            $upgradeOptions = [];
        }

        if ($host instanceof ColonyInterface) {
            $game->setTemplateVar('COLONY', $host);
            $game->setTemplateVar('FORM_ACTION', 'colony.php');
            $game->setTemplateVar('SHIP_BUILD_PROGRESS', $this->colonyShipQueueRepository->getByColonyAndMode($host->getId(), 1));
            $game->setTemplateVar('SHIP_RETROFIT_PROGRESS', $this->colonyShipQueueRepository->getByColonyAndMode($host->getId(), 2));

            $shipRepairProgress = array_map(
                fn(ColonyShipRepairInterface $repair): ShipWrapperInterface => $this->spacecraftWrapperFactory->wrapShip($repair->getShip()),
                $this->colonyShipRepairRepository->getByColonyField(
                    $host->getId(),
                    $field->getFieldId()
                )
            );
            $game->setTemplateVar('SHIP_REPAIR_PROGRESS', $shipRepairProgress);

            $terraFormingState = $this->colonyTerraformingRepository->getByColonyAndField(
                $host->getId(),
                $field->getId()
            );

            $terraFormingBar = null;
            if ($terraFormingState !== null) {
                $terraFormingBar = $this->statusBarFactory
                    ->createStatusBar()
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

        if ($building !== null) {
            $game->setTemplateVar(
                'BUILDING_FUNCTION',
                $this->colonyLibFactory->createBuildingFunctionWrapper($building->getFunctions()->toArray())
            );
            if ($field->isUnderConstruction()) {
                $game->setTemplateVar('CONSTRUCTION_STATUS_BAR', $this->getConstructionStatusBar($field, $building));
            }
        }
        $game->setTemplateVar(
            'HAS_UPGRADE_OR_TERRAFORMING_OPTION',
            (
                !$field->isUnderConstruction()
                && $upgradeOptions !== []
            ) || (
                $terraformingOptions !== []
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

    private function getConstructionStatusBar(PlanetFieldInterface $field, BuildingInterface $building): string
    {
        return $this->statusBarFactory
            ->createStatusBar()
            ->setColor(StatusBarColorEnum::STATUSBAR_GREEN)
            ->setLabel(_('Fortschritt'))
            ->setMaxValue($building->getBuildtime())
            ->setValue($field->getBuildProgress())
            ->render();
    }
}
