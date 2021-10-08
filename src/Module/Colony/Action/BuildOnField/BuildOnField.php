<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildOnField;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowBuildResult\ShowBuildResult;
use Stu\Orm\Entity\BuildingCostInterface;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class BuildOnField implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILD';

    private ColonyLoaderInterface $colonyLoader;

    private BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private BuildingActionInterface $buildingAction;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository,
        ResearchedRepositoryInterface $researchedRepository,
        BuildingRepositoryInterface $buildingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        BuildingActionInterface $buildingAction,
        EntityManagerInterface $entityManager
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildingFieldAlternativeRepository = $buildingFieldAlternativeRepository;
        $this->researchedRepository = $researchedRepository;
        $this->buildingRepository = $buildingRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->buildingAction = $buildingAction;
        $this->entityManager = $entityManager;
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

        $field = $this->planetFieldRepository->getByColonyAndFieldId($colonyId, (int) request::indInt('fid'));

        if ($field === null) {
            return;
        }

        if ($field->getTerraformingId() > 0) {
            return;
        }
        $building = $this->buildingRepository->find((int) request::indInt('bid'));
        if ($building === null) {
            return;
        }

        $buildingId = $building->getId();
        $researchId = $building->getResearchId();

        if ($researchId > 0 && $this->researchedRepository->hasUserFinishedResearch($user, [$researchId]) === false) {
            return;
        }
        if ($building->getBuildableFields()->containsKey((int) $field->getFieldType()) === false) {
            return;
        }

        $researchId = $building->getBuildableFields()->get((int) $field->getFieldType())->getResearchId();
        if ($researchId != null && $this->researchedRepository->hasUserFinishedResearch($user, [$researchId]) === false) {
            return;
        }

        $isMoon = $colony->getPlanetType()->getIsMoon();
        $isOrbitField = $isMoon ? $field->getFieldId() < 14 : $field->getFieldId() < 20;
        if ($isOrbitField && !$colony->getBlockers()->isEmpty()) {
            $game->addInformation(_('Der Orbit kann nicht bebaut werden während die Kolonie blockiert wird'));
            return;
        }

        if (
            $building->hasLimitColony() &&
            $this->planetFieldRepository->getCountByColonyAndBuilding($colonyId, $buildingId) >= $building->getLimitColony()
        ) {
            $game->addInformationf(
                _('Dieses Gebäude kann auf dieser Kolonie nur %d mal gebaut werden'),
                $building->getLimitColony()
            );
            return;
        }
        if ($building->hasLimit() && $this->planetFieldRepository->getCountByBuildingAndUser($buildingId, $userId) >= $building->getLimit()) {
            $game->addInformationf(
                _('Dieses Gebäude kann insgesamt nur %d mal gebaut werden'),
                $building->getLimit()
            );
            return;
        }

        // Check for alternative building
        $alt_building = $this->buildingFieldAlternativeRepository->getByBuildingAndFieldType(
            $buildingId,
            (int) $field->getFieldType()
        );
        if ($alt_building !== null) {
            $building = $alt_building->getAlternativeBuilding();
        }

        $storage = $colony->getStorage();
        foreach ($building->getCosts() as $obj) {
            $commodityId = $obj->getGoodId();

            $currentBuildingCost = [];

            if ($field->hasBuilding()) {

                $currentBuildingCost = $field->getBuilding()->getCosts()->toArray();

                $result = array_filter(
                    $currentBuildingCost,
                    function (BuildingCostInterface $buildingCost) use ($commodityId): bool {
                        return $commodityId === $buildingCost->getGoodId();
                    }
                );

                if (
                    !$storage->containsKey($commodityId) &&
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
                if (!$storage->containsKey($commodityId)) {
                    $game->addInformationf(
                        _('Es werden %s %s benötigt - Es ist jedoch keines vorhanden'),
                        $obj->getAmount(),
                        $obj->getGood()->getName()
                    );
                    return;
                }
            }
            if (!$storage->containsKey($commodityId)) {
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
            $this->buildingAction->remove($colony, $field, $game);
        }

        $this->colonyRepository->save($colony);
        $this->entityManager->flush();
        $colony = $this->colonyRepository->find(request::indInt('id'));

        foreach ($building->getCosts() as $obj) {
            $this->colonyStorageManager->lowerStorage($colony, $obj->getGood(), $obj->getAmount());
        }

        $colony->lowerEps($building->getEpsCost());
        $field->setBuilding($building);
        $field->setActive(time() + $building->getBuildtime());
        $field->setActivateAfterBuild(true);

        $this->colonyRepository->save($colony);

        $this->planetFieldRepository->save($field);

        $game->addInformationf(
            _('%s wird gebaut - Fertigstellung: %s'),
            $building->getName(),
            date('d.m.Y H:i', $field->getBuildtime())
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
