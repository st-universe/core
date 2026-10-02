<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildOnField;

use request;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Colony\View\ShowInformation\ShowInformation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingCost;
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

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowInformation::VIEW_IDENTIFIER);

        $user = $context->getUser();
        $userId = $user->getId();

        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($context->getUser());
        $host = $field->getHost();

        if ($field->getTerraforming() !== null) {
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
            $context->getInfo()->addInformationf(
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
            $context->getInfo()->addInformationf(
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
                if (!$this->checkBuildingCosts($host, $building, $field, $context)) {
                    return;
                }
                if ($changeable->getEps() < $building->getEpsCost()) {
                    $context->getInfo()->addInformationf(
                        _('Zum Bau wird %d Energie benötigt - Vorhanden ist nur %d'),
                        $building->getEpsCost(),
                        $changeable->getEps()
                    );
                    return;
                }

                if ($changeable->getEps() > $host->getMaxEps() - $currentBuilding->getEpsStorage()
                && $host->getMaxEps() - $currentBuilding->getEpsStorage() < $building->getEpsCost()) {
                    $context->getInfo()->addInformation(_('Nach der Demontage steht nicht mehr genügend Energie zum Bau zur Verfügung'));
                    return;
                }
            }

            $this->buildingAction->remove($field, $context);

            $context->addExecuteJS(sprintf("refreshHost('%s');", $context->getSessionString()));

            $this->componentRegistration
                ->addComponentUpdate(ColonyComponentEnum::SHIELDING, $host)
                ->addComponentUpdate(ColonyComponentEnum::EPS_BAR, $host)
                ->addComponentUpdate(ColonyComponentEnum::STORAGE, $host);
        }

        if ($host instanceof Colony && !$this->doColonyChecksAndConsume($field, $building, $host, $context)) {
            return;
        }

        $field->setBuilding($building);
        $field->setActivateAfterBuild(true);

        $context->addExecuteJS(sprintf("refreshHost('%s');", $context->getSessionString()));

        $this->componentRegistration
            ->addComponentUpdate(ColonyComponentEnum::SHIELDING, $host)
            ->addComponentUpdate(ColonyComponentEnum::EPS_BAR, $host)
            ->addComponentUpdate(ColonyComponentEnum::STORAGE, $host);

        if ($host instanceof ColonySandbox) {
            $this->buildingManager->finish($field);

            $context->getInfo()->addInformationf(
                _('%s wurde gebaut'),
                $building->getName()
            );
        } else {
            $this->planetFieldRepository->save($field);

            $context->getInfo()->addInformationf(
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
        ActionControllerContext $context
    ): bool {
        if (
            $this->planetFieldTypeRetriever->isOrbitField($field)
            && $colony->isBlocked()
        ) {
            $context->getInfo()->addInformation(_('Der Orbit kann nicht bebaut werden während die Kolonie blockiert wird'));
            return false;
        }

        //check for sufficient commodities
        if (!$this->checkBuildingCosts($colony, $building, $field, $context)) {
            return false;
        }

        $changeable = $colony->getChangeable();

        if ($changeable->getEps() < $building->getEpsCost()) {
            $context->getInfo()->addInformationf(
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
        ActionControllerContext $context
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
                    fn (BuildingCost $buildingCost): bool => $commodityId === $buildingCost->getCommodityId()
                );
                if (
                    !$storages->containsKey($commodityId) &&
                    $result === []
                ) {
                    $context->getInfo()->addInformationf(
                        _('Es werden %d %s benötigt - Es ist jedoch keines vorhanden'),
                        $cost->getAmount(),
                        $cost->getCommodity()->getName()
                    );
                    $isEnoughAvailable = false;
                    continue;
                }
            } elseif (!$storages->containsKey($commodityId)) {
                $context->getInfo()->addInformationf(
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
                    fn (BuildingCost $buildingCost): bool => $commodityId === $buildingCost->getCommodityId()
                );
                if ($result !== []) {
                    $amount += current($result)->getHalfAmount();
                }
            }
            if ($cost->getAmount() > $amount) {
                $context->getInfo()->addInformationf(
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

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
