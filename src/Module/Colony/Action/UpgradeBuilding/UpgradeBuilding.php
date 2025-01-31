<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\UpgradeBuilding;

use Override;
use request;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\BuildingUpgradeCostInterface;
use Stu\Orm\Entity\BuildingUpgradeInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingUpgradeRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class UpgradeBuilding implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UPGRADE_BUILDING';

    public function __construct(
        private BuildingUpgradeRepositoryInterface $buildingUpgradeRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository,
        private ResearchedRepositoryInterface $researchedRepository,
        private PlanetFieldHostProviderInterface $planetFieldHostProvider,
        private StorageManagerInterface $storageManager,
        private ColonyRepositoryInterface $colonyRepository,
        private BuildingActionInterface $buildingAction,
        private BuildingManagerInterface $buildingManager,
        private ComponentRegistrationInterface $componentRegistration
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowInformation::VIEW_IDENTIFIER);

        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser());
        $host = $field->getHost();

        // has to be string because of bigint issue
        $upgradeId = request::postIntFatal('buildingid');

        $upgrade = $this->buildingUpgradeRepository->find($upgradeId);
        if ($upgrade === null) {
            return;
        }

        if ($upgrade->getUpgradeFromBuildingId() != $field->getBuildingId()) {
            return;
        }

        $researchId = $upgrade->getResearchId();
        if (
            $researchId > 0 &&
            $this->researchedRepository->hasUserFinishedResearch($game->getUser(), [$researchId]) === false
        ) {
            return;
        }
        if ($field->isUnderConstruction()) {
            $game->addInformation(_('Das Gebäude auf diesem Feld ist noch nicht fertig'));
            return;
        }

        if ($host instanceof ColonyInterface && !$this->doColonyCheckAndConsumeEnergy($upgrade, $host, $game)) {
            return;
        }

        // Check for alternative building
        $alt_building = $this->buildingFieldAlternativeRepository->getByBuildingAndFieldType(
            $upgrade->getBuilding()->getId(),
            $field->getFieldType()
        );
        $building = $alt_building !== null ? $alt_building->getAlternativeBuilding() : $upgrade->getBuilding();

        $isActive = $field->isActive();
        $this->buildingAction->remove($field, $game, true);

        if ($host instanceof ColonyInterface) {
            foreach ($upgrade->getUpgradeCosts() as $obj) {
                $this->storageManager->lowerStorage($host, $obj->getCommodity(), $obj->getAmount());
            }
        }

        $field->setBuilding($building);
        $field->setActivateAfterBuild($isActive);

        $game->addExecuteJS('refreshHost();');

        $this->componentRegistration
            ->addComponentUpdate(ColonyComponentEnum::SHIELDING, $host)
            ->addComponentUpdate(ColonyComponentEnum::EPS_BAR, $host)
            ->addComponentUpdate(ColonyComponentEnum::STORAGE, $host);

        if ($host instanceof ColonySandboxInterface) {
            $this->buildingManager->finish($field);

            $game->addInformationf(
                _('%s wurde gebaut'),
                $building->getName()
            );
        } else {
            $field->setActive(time() + $building->getBuildtime());

            $game->addInformationf(
                _('%s wird durchgeführt - Fertigstellung: %s'),
                $upgrade->getDescription(),
                date('d.m.Y H:i', $field->getBuildtime())
            );
        }

        $this->planetFieldRepository->save($field);
    }

    private function doColonyCheckAndConsumeEnergy(BuildingUpgradeInterface $upgrade, ColonyInterface $colony, GameControllerInterface $game): bool
    {
        $storage = $colony->getStorage();

        /** @var BuildingUpgradeCostInterface $obj */
        foreach ($upgrade->getUpgradeCosts() as $obj) {
            if (!$storage->containsKey($obj->getCommodityId())) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Es ist jedoch keines vorhanden'),
                    $obj->getAmount(),
                    $obj->getCommodity()->getName()
                );
                return false;
            }
            if ($obj->getAmount() > $storage[$obj->getCommodityId()]->getAmount()) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Vorhanden sind nur %d'),
                    $obj->getAmount(),
                    $obj->getCommodity()->getName(),
                    $storage[$obj->getCommodityId()]->getAmount()
                );
                return false;
            }
        }

        if ($colony->getEps() < $upgrade->getEnergyCost()) {
            $game->addInformationf(
                _('Zum Bau wird %d Energie benötigt - Vorhanden ist nur %d'),
                $upgrade->getEnergyCost(),
                $colony->getEps()
            );
            return false;
        }

        $colony->lowerEps($upgrade->getEnergyCost());
        $this->colonyRepository->save($colony);

        return true;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
