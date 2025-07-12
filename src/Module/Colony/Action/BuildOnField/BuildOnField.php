<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildOnField;

use Override;
use request;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\BuildingCost;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\BuildingFieldAlternativeRepositoryInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class BuildOnField implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BUILD';

    public function __construct(
        private PlanetFieldHostProviderInterface $planetFieldHostProvider,
        private BuildingFieldAlternativeRepositoryInterface $buildingFieldAlternativeRepository,
        private ResearchedRepositoryInterface $researchedRepository,
        private BuildingRepositoryInterface $buildingRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private StorageManagerInterface $storageManager,
        private ColonyRepositoryInterface $colonyRepository,
        private BuildingActionInterface $buildingAction,
        private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        private BuildingManagerInterface $buildingManager,
        private ComponentRegistrationInterface $componentRegistration
    ) {}

    #[Override]
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
        $building = $this->buildingRepository->find(request::indInt('buildingid'));
        if ($building === null) {
            return;
        }

        $buildingId = $building->getId();
        $researchId = $building->getResearchId();

        if ($building->getBuildableFields()->containsKey($field->getFieldType()) === false) {
            return;
        }

        if ($userId !== UserConstants::USER_NOONE) {
            if ($researchId > 0 && $this->researchedRepository->hasUserFinishedResearch($user, [$researchId]) === false) {
                return;
            }

            $researchId = $building->getBuildableFields()->get($field->getFieldType())?->getResearchId();
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
        if (
            $host instanceof Colony
            && $building->hasLimit()
            && $this->planetFieldRepository->getCountByBuildingAndUser($buildingId, $userId) >= $building->getLimit()
        ) {
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

        $currentBuilding = $field->getBuilding();
        if ($currentBuilding !== null) {

            if ($host instanceof Colony) {

                $changeable = $host->getChangeable();

                if (!$this->checkBuildingCosts($host, $building, $field, $game)) {
                    return;
                } elseif ($changeable->getEps() < $building->getEpsCost()) {
                    $game->addInformationf(
                        _('Zum Bau wird %d Energie benötigt - Vorhanden ist nur %d'),
                        $building->getEpsCost(),
                        $changeable->getEps()
                    );
                    return;
                } elseif (
                    $changeable->getEps() > $host->getMaxEps() - $currentBuilding->getEpsStorage()
                    && $host->getMaxEps() - $currentBuilding->getEpsStorage() < $building->getEpsCost()
                ) {
                    $game->addInformation(_('Nach der Demontage steht nicht mehr genügend Energie zum Bau zur Verfügung'));
                    return;
                }
            }

            $this->buildingAction->remove($field, $game);

            $game->addExecuteJS(sprintf("refreshHost('%s');", $game->getSessionString()));

            $this->componentRegistration
                ->addComponentUpdate(ColonyComponentEnum::SHIELDING, $host)
                ->addComponentUpdate(ColonyComponentEnum::EPS_BAR, $host)
                ->addComponentUpdate(ColonyComponentEnum::STORAGE, $host);
        }

        if ($host instanceof Colony && !$this->doColonyChecksAndConsume($field, $building, $host, $game)) {
            return;
        }

        $field->setBuilding($building);
        $field->setActivateAfterBuild(true);

        $game->addExecuteJS(sprintf("refreshHost('%s');", $game->getSessionString()));

        $this->componentRegistration
            ->addComponentUpdate(ColonyComponentEnum::SHIELDING, $host)
            ->addComponentUpdate(ColonyComponentEnum::EPS_BAR, $host)
            ->addComponentUpdate(ColonyComponentEnum::STORAGE, $host);

        if ($host instanceof ColonySandbox) {
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
        PlanetField $field,
        Building $building,
        Colony $colony,
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

        $changeable = $colony->getChangeable();

        if ($changeable->getEps() < $building->getEpsCost()) {
            $game->addInformationf(
                _('Zum Bau wird %d Energie benötigt - Vorhanden ist nur %d'),
                $building->getEpsCost(),
                $changeable->getEps()
            );
            return false;
        }

        foreach ($building->getCosts() as $cost) {
            $this->storageManager->lowerStorage($colony, $cost->getCommodity(), $cost->getAmount());
        }

        $changeable->lowerEps($building->getEpsCost());
        $field->setActive(time() + $building->getBuildtime());

        $this->colonyRepository->save($colony);

        return true;
    }

    private function checkBuildingCosts(
        Colony $colony,
        Building $building,
        PlanetField $field,
        GameControllerInterface $game
    ): bool {
        $isEnoughAvailable = true;
        $storages = $colony->getStorage();

        foreach ($building->getCosts() as $cost) {
            $commodityId = $cost->getCommodityId();

            $currentBuildingCost = [];
            $currentBuilding = $field->getBuilding();

            if ($currentBuilding !== null) {
                $currentBuildingCost = $currentBuilding->getCosts()->toArray();
                $result = array_filter(
                    $currentBuildingCost,
                    fn(BuildingCost $buildingCost): bool => $commodityId === $buildingCost->getCommodityId()
                );
                if (
                    !$storages->containsKey($commodityId) &&
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
            } elseif (!$storages->containsKey($commodityId)) {
                $game->addInformationf(
                    _('Es werden %s %s benötigt - Es ist jedoch keines vorhanden'),
                    $cost->getAmount(),
                    $cost->getCommodity()->getName()
                );
                $isEnoughAvailable = false;
                continue;
            }
            $storage = $storages->get($commodityId);
            $amount = $storage !== null ? $storage->getAmount() : 0;
            if ($field->hasBuilding()) {
                $result = array_filter(
                    $currentBuildingCost,
                    fn(BuildingCost $buildingCost): bool => $commodityId === $buildingCost->getCommodityId()
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
