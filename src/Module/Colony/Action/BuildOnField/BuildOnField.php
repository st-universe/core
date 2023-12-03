<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildOnField;

use request;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\BuildingCostInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class BuildOnField implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILD';

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private BuildingActionInterface $buildingAction;

    private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever;

    private BuildingManagerInterface $buildingManager;

    public function __construct(
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository,
        ResearchedRepositoryInterface $researchedRepository,
        BuildingRepositoryInterface $buildingRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        BuildingActionInterface $buildingAction,
        PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        BuildingManagerInterface $buildingManager
    ) {
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->buildingFieldAlternativeRepository = $buildingFieldAlternativeRepository;
        $this->researchedRepository = $researchedRepository;
        $this->buildingRepository = $buildingRepository;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->buildingAction = $buildingAction;
        $this->planetFieldTypeRetriever = $planetFieldTypeRetriever;
        $this->buildingManager = $buildingManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowInformation::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($game->getUser());
        $host = $field->getHost();

        if ($field->getTerraformingId() > 0) {
            return;
        }
        $building = $this->buildingRepository->find(request::indInt('bid'));
        if ($building === null) {
            return;
        }

        $buildingId = $building->getId();
        $researchId = $building->getResearchId();

        if ($building->getBuildableFields()->containsKey($field->getFieldType()) === false) {
            return;
        }

        if ($userId !== UserEnum::USER_NOONE) {
            if ($researchId > 0 && $this->researchedRepository->hasUserFinishedResearch($user, [$researchId]) === false) {
                return;
            }

            $researchId = $building->getBuildableFields()->get($field->getFieldType())->getResearchId();
            if ($researchId != null && $this->researchedRepository->hasUserFinishedResearch($user, [$researchId]) === false) {
                return;
            }
        }

        if (
            $building->hasLimitColony() &&
            $this->planetFieldRepository->getCountByHostAndBuilding($host, $buildingId) >= $building->getLimitColony()
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
            $field->getFieldType()
        );
        if ($alt_building !== null) {
            $building = $alt_building->getAlternativeBuilding();
        }

        if ($host instanceof ColonyInterface) {
            if (!$this->doColonyChecksAndConsume($field, $building, $host, $game)) {
                return;
            }
        }

        $field->setBuilding($building);
        $field->setActivateAfterBuild(true);

        $game->addExecuteJS('refreshHost();');

        if ($host instanceof ColonySandboxInterface) {
            $this->buildingManager->finish($field);

            $game->addInformationf(
                _('%s wurde gebaut'),
                $building->getName()
            );
        } else {
            $this->planetFieldRepository->save($field);

            $game->addInformationf(
                _('%s wird gebaut - Fertigstellung: %s'),
                $building->getName(),
                date('d.m.Y H:i', $field->getActive())
            );
        }
    }

    private function doColonyChecksAndConsume(
        PlanetFieldInterface $field,
        BuildingInterface $building,
        ColonyInterface $colony,
        GameControllerInterface $game
    ): bool {
        if (
            $this->planetFieldTypeRetriever->isOrbitField($field)
            && $colony->isBlocked()
        ) {
            $game->addInformation(_('Der Orbit kann nicht bebaut werden während die Kolonie blockiert wird'));
            return false;
        }

        //check for sufficient commodities
        if (!$this->checkBuildingCosts($colony, $building, $field, $game)) {
            return false;
        }

        if ($colony->getEps() < $building->getEpsCost()) {
            $game->addInformationf(
                _('Zum Bau wird %d Energie benötigt - Vorhanden ist nur %d'),
                $building->getEpsCost(),
                $colony->getEps()
            );
            return false;
        }

        if ($field->hasBuilding()) {
            if ($colony->getEps() > $colony->getMaxEps() - $field->getBuilding()->getEpsStorage() && $colony->getMaxEps() - $field->getBuilding()->getEpsStorage() < $building->getEpsCost()) {
                $game->addInformation(_('Nach der Demontage steht nicht mehr genügend Energie zum Bau zur Verfügung'));
                return false;
            }
            $this->buildingAction->remove($field, $game);
        }

        foreach ($building->getCosts() as $cost) {
            $this->colonyStorageManager->lowerStorage($colony, $cost->getCommodity(), $cost->getAmount());
        }

        $colony->lowerEps($building->getEpsCost());
        $field->setActive(time() + $building->getBuildtime());

        $this->colonyRepository->save($colony);

        return true;
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
                    fn (BuildingCostInterface $buildingCost): bool => $commodityId === $buildingCost->getCommodityId()
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
            } elseif (!$storage->containsKey($commodityId)) {
                $game->addInformationf(
                    _('Es werden %s %s benötigt - Es ist jedoch keines vorhanden'),
                    $cost->getAmount(),
                    $cost->getCommodity()->getName()
                );
                $isEnoughAvailable = false;
                continue;
            }
            $amount = $storage->containsKey($commodityId) ? $storage[$commodityId]->getAmount() : 0;
            if ($field->hasBuilding()) {
                $result = array_filter(
                    $currentBuildingCost,
                    fn (BuildingCostInterface $buildingCost): bool => $commodityId === $buildingCost->getCommodityId()
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
