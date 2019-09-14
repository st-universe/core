<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildOnField;

use ColfieldData;
use Colfields;
use ColonyData;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowBuildResult\ShowBuildResult;
use Stu\Orm\Entity\BuildingCostInterface;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class BuildOnField implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILD';

    private $colonyLoader;

    private $buildingFieldAlternativeRepository;

    private $researchedRepository;

    private $buildingRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository,
        ResearchedRepositoryInterface $researchedRepository,
        BuildingRepositoryInterface $buildingRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildingFieldAlternativeRepository = $buildingFieldAlternativeRepository;
        $this->researchedRepository = $researchedRepository;
        $this->buildingRepository = $buildingRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowBuildResult::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $colonyId = $colony->getId();

        $field = Colfields::getByColonyField(
            (int)request::indInt('fid'),
            $colonyId
        );

        if ($field->getTerraformingId() > 0) {
            return;
        }
        $building = $this->buildingRepository->find((int) request::indInt('bid'));
        if ($building === null) {
            return;
        }

        $buildingId = $building->getId();
        $researchId = $building->getResearchId();

        if ($researchId > 0 && $this->researchedRepository->hasUserFinishedResearch($researchId, $userId) === false) {
            return;
        }
        if ($building->getBuildableFields()->containsKey((int) $field->getFieldType()) === false) {
            return;
        }
        if ($building->hasLimitColony() && Colfields::countInstances('buildings_id=' . $buildingId . ' AND colonies_id=' . $colonyId) >= $building->getLimitColony()) {
            $game->addInformationf(
                _('Dieses Gebäude kann auf dieser Kolonie nur %d mal gebaut werden'),
                $building->getLimitColony()
            );
            return;
        }
        if ($building->hasLimit() && Colfields::countInstances('buildings_id=' . $buildingId . ' AND colonies_id IN (SELECT id FROM stu_colonies WHERE user_id=' . $userId . ')') >= $building->getLimit()) {
            $game->addInformationf(
                _('Dieses Gebäude kann insgesamt nur %d mal gebaut werden'),
                $building->getLimit()
            );
            return;
        }
        $storage = $colony->getStorage();
        foreach ($building->getCosts() as $obj) {
            $commodityId = $obj->getGoodId();

            if ($field->hasBuilding()) {

                $currentBuildingCost = $field->getBuilding()->getCosts();

                $result = array_filter(
                    $currentBuildingCost,
                    function (BuildingCostInterface $buildingCost) use ($commodityId): bool {
                        return $commodityId === $buildingCost->getGoodId();
                    }
                );

                if (
                    !array_key_exists($commodityId, $storage) &&
                    $result === []
                ) {
                    $game->addInformationf(
                        _('Es werden %d %s benötigt - Es ist jedoch keines vorhanden'),
                        $obj->getAmount(),
                        $obj->getGood()->getName()
                    );
                    return;
                }
            } else {
                if (!array_key_exists($commodityId, $storage)) {
                    $game->addInformationf(
                        _('Es werden %s %s benötigt - Es ist jedoch keines vorhanden'),
                        $obj->getAmount(),
                        $obj->getGood()->getName()
                    );
                    return;
                }
            }
            if (!isset($storage[$commodityId])) {
                $amount = 0;
            } else {
                $amount = $storage[$commodityId]->getAmount();
            }
            if ($field->hasBuilding()) {
                $result = array_filter(
                    $currentBuildingCost,
                    function (BuildingCostInterface $buildingCost) use ($commodityId): bool {
                        return $commodityId === $buildingCost->getGoodId();
                    }
                );
                if ($result !== []) {
                    $amount += current($result)->getHalfAmount();
                }
            }
            if ($obj->getAmount() > $amount) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Vorhanden sind nur %d'),
                    $obj->getAmount(),
                    $obj->getGood()->getName(),
                    $amount
                );
                return;
            }
        }

        if ($colony->getEps() < $building->getEpsCost()) {
            $game->addInformationf(
                _('Zum Bau wird %d Energie benötigt - Vorhanden ist nur %d'),
                $building->getEpsCost(),
                $colony->getEps()
            );
            return;
        }

        if ($field->hasBuilding()) {
            if ($colony->getEps() > $colony->getMaxEps() - $field->getBuilding()->getEpsStorage()) {
                if ($colony->getMaxEps() - $field->getBuilding()->getEpsStorage() < $building->getEpsCost()) {
                    $game->addInformation(_('Nach der Demontage steht nicht mehr genügend Energie zum Bau zur Verfügung'));
                    return;
                }
            }
            $this->removeBuilding($field, $colony, $game);
        }

        foreach ($building->getCosts() as $obj) {
            $colony->lowerStorage($obj->getGoodId(), $obj->getAmount());
        }
        // Check for alternative building
        $alt_building = $this->buildingFieldAlternativeRepository->getByBuildingAndFieldType(
            $buildingId,
            (int) $field->getFieldType()
        );
        if ($alt_building !== null) {
            $building = $alt_building->getAlternativeBuilding();
        }

        $colony->lowerEps($building->getEpsCost());
        $field->setBuildingId($building->getId());
        $field->setBuildtime($building->getBuildtime());
        $colony->save();
        $field->save();
        $game->addInformationf(
            _('%s wird gebaut - Fertigstellung: %s'),
            $building->getName(),
            date('d.m.Y H:i', $field->getBuildtime())
        );
    }

    private function removeBuilding(ColfieldData $field, ColonyData $colony, GameControllerInterface $game)
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
            _('%s auf Feld %d wurde demontiert'),
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
        $field->save();
        $colony->save();
    }

    protected function deActivateBuilding(ColfieldData $field, ColonyData $colony, GameControllerInterface $game)
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
        $field->save();
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
