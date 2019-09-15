<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\UpgradeBuilding;

use ColonyData;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Entity\BuildingUpgradeCostInterface;
use Stu\Orm\Entity\BuildingUpgradeInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingUpgradeRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class UpgradeBuilding implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_UPGRADE_BUILDING';

    private $colonyLoader;

    private $buildingUpgradeRepository;

    private $buildingFieldAlternativeRepository;

    private $researchedRepository;

    private $planetFieldRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildingUpgradeRepositoryInterface $buildingUpgradeRepository,
        BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository,
        ResearchedRepositoryInterface $researchedRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildingUpgradeRepository = $buildingUpgradeRepository;
        $this->buildingFieldAlternativeRepository = $buildingFieldAlternativeRepository;
        $this->researchedRepository = $researchedRepository;
        $this->planetFieldRepository = $planetFieldRepository;
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
            $this->researchedRepository->hasUserFinishedResearch($researchId, $game->getUser()->getId()) === false
        ) {
            return;
        }
        if ($field->isInConstruction()) {
            $game->addInformation(_('Das Gebäude auf diesem Feld ist noch nicht fertig'));
            return;
        }
        $storage = $colony->getStorage();

        /** @var BuildingUpgradeCostInterface $obj */
        foreach ($upgrade->getUpgradeCosts() as $key => $obj) {
            if (!array_key_exists($obj->getGoodId(), $storage)) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Es ist jedoch keines vorhanden'),
                    $obj->getAmount(),
                    $obj->getGood()->getName()
                );
                return;
            }
            if ($obj->getAmount() > $storage[$obj->getGoodId()]->getAmount()) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Vorhanden sind nur %d'),
                    $obj->getAmount(),
                    $obj->getGood()->getName(),
                    $storage[$obj->getGoodId()]->getAmount()
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
        $this->removeBuilding($field, $colony, $game);

        foreach ($upgrade->getUpgradeCosts() as $key => $obj) {
            $colony->lowerStorage($obj->getGoodId(), $obj->getAmount());
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
        $field->setActive($building->getBuildtime());
        $colony->save();

        $this->planetFieldRepository->save($field);

        $game->addInformationf(
            _('%s wird durchgeführt - Fertigstellung: %s'),
            $upgrade->getDescription(),
            date('d.m.Y H:i', $field->getBuildtime())
        );
    }

    private function removeBuilding(PlanetFieldInterface $field, ColonyData $colony, GameControllerInterface $game)
    {
        if (!$field->hasBuilding()) {
            return;
        }
        if (!$field->getBuilding()->isRemoveAble()) {
            return;
        }
        $this->deActivateBuilding($field, $colony, $game);
        $colony->lowerMaxStorage($field->getBuilding()->getStorage());
        $colony->lowerMaxEps($field->getBuilding()->getEpsStorage());
        $game->addInformationf(
            _('%s auf Feld %s wurde demontiert'),
            $field->getBuilding()->getName(),
            $field->getFieldId()
        );
        $game->addInformation(_('Es konnten folgende Waren recycled werden'));
        foreach ($field->getBuilding()->getCosts() as $value) {
            $halfAmount = $value->getHalfAmount();
            if ($colony->getStorageSum() + $halfAmount > $colony->getMaxStorage()) {
                $amount = $colony->getMaxStorage() - $colony->getStorageSum();
            } else {
                $amount = $halfAmount;
            }
            if ($amount <= 0) {
                break;
            }
            $colony->upperStorage($value->getGoodId(), $amount);
            $game->addInformationf('%d %s', $amount, $value->getGood()->getName());
        }
        $field->clearBuilding();

        $this->planetFieldRepository->save($field);

        $colony->save();
    }

    protected function deActivateBuilding(PlanetFieldInterface $field, ColonyData $colony, GameControllerInterface $game)
    {
        if (!$field->hasBuilding()) {
            return;
        }
        if (!$field->isActivateAble()) {
            return;
        }
        if (!$field->isActive()) {
            return;
        }
        $colony->upperWorkless($field->getBuilding()->getWorkers());
        $colony->lowerWorkers($field->getBuilding()->getWorkers());
        $colony->lowerMaxBev($field->getBuilding()->getHousing());
        $field->setActive(0);

        $this->planetFieldRepository->save($field);

        $colony->save();
        $field->getBuilding()->postDeactivation($colony);

        $game->addInformationf(
            _('%s auf Feld %d wurde deaktiviert'),
            $field->getBuilding()->getName(),
            $field->getFieldId()
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
