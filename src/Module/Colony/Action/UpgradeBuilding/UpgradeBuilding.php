<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\UpgradeBuilding;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\BuildingUpgradeCostInterface;
use Stu\Orm\Entity\BuildingUpgradeInterface;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingUpgradeRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class UpgradeBuilding implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_UPGRADE_BUILDING';

    private ColonyLoaderInterface $colonyLoader;

    private BuildingUpgradeRepositoryInterface $buildingUpgradeRepository;

    private BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private BuildingActionInterface $buildingAction;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildingUpgradeRepositoryInterface $buildingUpgradeRepository,
        BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository,
        ResearchedRepositoryInterface $researchedRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        BuildingActionInterface $buildingAction
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildingUpgradeRepository = $buildingUpgradeRepository;
        $this->buildingFieldAlternativeRepository = $buildingFieldAlternativeRepository;
        $this->researchedRepository = $researchedRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->buildingAction = $buildingAction;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $user->getId()
        );
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            (int)request::indInt('fid')
        );

        // has to be string because of bigint issue
        $upgradeId = request::getStringFatal('upid');

        /**
         * @var BuildingUpgradeInterface
         */
        $upgrade = $this->buildingUpgradeRepository->find($upgradeId);
        if ($upgrade === null) {
            return;
        }

        if ($upgrade->getUpgradeFromBuildingId() != $field->getBuildingId()) {
            return;
        }

        $researchId = (int) $upgrade->getResearchId();
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
        $storage = $colony->getStorage();

        /** @var BuildingUpgradeCostInterface $obj */
        foreach ($upgrade->getUpgradeCosts() as $obj) {
            if (!$storage->containsKey($obj->getCommodityId())) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Es ist jedoch keines vorhanden'),
                    $obj->getAmount(),
                    $obj->getCommodity()->getName()
                );
                return;
            }
            if ($obj->getAmount() > $storage[$obj->getCommodityId()]->getAmount()) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Vorhanden sind nur %d'),
                    $obj->getAmount(),
                    $obj->getCommodity()->getName(),
                    $storage[$obj->getCommodityId()]->getAmount()
                );
                return;
            }
        }

        if ($colony->getEps() < $upgrade->getEnergyCost()) {
            $game->addInformationf(
                _('Zum Bau wird %d Energie benötigt - Vorhanden ist nur %d'),
                $upgrade->getEnergyCost(),
                $colony->getEps()
            );
            return;
        }

        $colony->lowerEps($upgrade->getEnergyCost());

        $this->buildingAction->remove($colony, $field, $game, true);

        foreach ($upgrade->getUpgradeCosts() as $obj) {
            $this->colonyStorageManager->lowerStorage($colony, $obj->getCommodity(), $obj->getAmount());
        }
        // Check for alternative building
        $alt_building = $this->buildingFieldAlternativeRepository->getByBuildingAndFieldType(
            (int) $upgrade->getBuilding()->getId(),
            (int) $field->getFieldType()
        );
        if ($alt_building !== null) {
            $building = $alt_building->getAlternativeBuilding();
        } else {
            $building = $upgrade->getBuilding();
        }

        $field->setBuilding($building);
        $field->setActive(time() + $building->getBuildtime());

        $this->colonyRepository->save($colony);
        $this->planetFieldRepository->save($field);

        $game->addInformationf(
            _('%s wird durchgeführt - Fertigstellung: %s'),
            $upgrade->getDescription(),
            date('d.m.Y H:i', $field->getBuildtime())
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
