<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildOnField;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Orm\Entity\BuildingCostInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
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
        $game->setView(ShowInformation::VIEW_IDENTIFIER);
        $game->addExecuteJS('refreshColony();');

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

        if ($building->getBuildableFields()->containsKey((int) $field->getFieldType()) === false) {
            return;
        }

        if ($userId !== GameEnum::USER_NOONE) {

            if ($researchId > 0 && $this->researchedRepository->hasUserFinishedResearch($user, [$researchId]) === false) {
                return;
            }

            $researchId = $building->getBuildableFields()->get((int) $field->getFieldType())->getResearchId();
            if ($researchId != null && $this->researchedRepository->hasUserFinishedResearch($user, [$researchId]) === false) {
                return;
            }
        }

        if ($field->isOrbit() && $colony->isBlocked()) {
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

        //check for sufficient commodities
        if ($userId !== GameEnum::USER_NOONE && !$this->checkBuildingCosts($colony, $building, $field, $game)) {
            return;
        }

        if ($userId !== GameEnum::USER_NOONE &&  $colony->getEps() < $building->getEpsCost()) {
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

        if ($userId !== GameEnum::USER_NOONE) {
            foreach ($building->getCosts() as $cost) {
                $this->colonyStorageManager->lowerStorage($colony, $cost->getCommodity(), $cost->getAmount());
            }

            $colony->lowerEps($building->getEpsCost());
            $field->setActive(time() + $building->getBuildtime());
        } else {
            $field->setActive(time() + 1);
        }

        $field->setBuilding($building);
        $field->setActivateAfterBuild(true);

        $this->colonyRepository->save($colony);

        $this->planetFieldRepository->save($field);

        $game->addInformationf(
            _('%s wird gebaut - Fertigstellung: %s'),
            $building->getName(),
            date('d.m.Y H:i', $field->getActive())
        );
    }

    private function checkBuildingCosts(
        ColonyInterface $colony,
        BuildingInterface $building,
        PlanetFieldInterface $field,
        GameControllerInterface $game
    ): bool {

        $isEnoughAvailable = true;
        $storage = $colony->getStorage();

        foreach ($building->getCosts() as $cost) {
            $commodityId = $cost->getCommodityId();

            $currentBuildingCost = [];

            if ($field->hasBuilding()) {

                $currentBuildingCost = $field->getBuilding()->getCosts()->toArray();

                $result = array_filter(
                    $currentBuildingCost,
                    function (BuildingCostInterface $buildingCost) use ($commodityId): bool {
                        return $commodityId === $buildingCost->getCommodityId();
                    }
                );

                if (
                    !$storage->containsKey($commodityId) &&
                    $result === []
                ) {
                    $game->addInformationf(
                        _('Es werden %d %s benötigt - Es ist jedoch keines vorhanden'),
                        $cost->getAmount(),
                        $cost->getCommodity()->getName()
                    );
                    $isEnoughAvailable = false;
                    continue;
                }
            } else {
                if (!$storage->containsKey($commodityId)) {
                    $game->addInformationf(
                        _('Es werden %s %s benötigt - Es ist jedoch keines vorhanden'),
                        $cost->getAmount(),
                        $cost->getCommodity()->getName()
                    );
                    $isEnoughAvailable = false;
                    continue;
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
                        return $commodityId === $buildingCost->getCommodityId();
                    }
                );
                if ($result !== []) {
                    $amount += current($result)->getHalfAmount();
                }
            }
            if ($cost->getAmount() > $amount) {
                $game->addInformationf(
                    _('Es werden %d %s benötigt - Vorhanden sind nur %d'),
                    $cost->getAmount(),
                    $cost->getCommodity()->getName(),
                    $amount
                );
                $isEnoughAvailable = false;
                continue;
            }
        }

        return $isEnoughAvailable;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
