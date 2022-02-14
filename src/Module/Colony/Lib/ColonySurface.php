<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Faction\FactionEnum;
use Stu\Module\Building\BuildingFunctionTypeEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\PlanetGenerator\PlanetGenerator;
use Stu\PlanetGenerator\PlanetGeneratorFileMissingException;

final class ColonySurface implements ColonySurfaceInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private EntityManagerInterface $entityManager;

    private LoggerUtilInterface $loggerUtil;

    private ColonyInterface $colony;

    private ?int $buildingId;

    private bool $showUnderground;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository,
        ColonyRepositoryInterface $colonyRepository,
        ResearchedRepositoryInterface $researchedRepository,
        EntityManagerInterface $entityManager,
        LoggerUtilInterface $loggerUtil,
        ColonyInterface $colony,
        ?int $buildingId = null,
        bool $showUnderground = true
    ) {
        $this->colony = $colony;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
        $this->buildingId = $buildingId;
        $this->colonyRepository = $colonyRepository;
        $this->researchedRepository = $researchedRepository;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtil;
        $this->showUnderground = $showUnderground;
    }

    public function getSurface(): array
    {
        if ($this->colony->isFree()) {

            try {
                $this->updateSurface();
            } catch (PlanetGeneratorFileMissingException $e) {
                return $this->colony->getPlanetFields()->toArray();
            }
        }

        $fields = $this->colony->getPlanetFields()->toArray();

        if (!$this->showUnderground) {
            $fields = array_filter(
                $fields,
                function (PlanetFieldInterface $field): bool {
                    return !$field->isUnderground();
                }
            );
        }

        if ($this->buildingId !== null) {
            $building = $this->buildingRepository->find($this->buildingId);

            if ($building === null) {
                $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
                $this->loggerUtil->log(sprintf('Es kommt gleich bei colonyId %d zu einem Fehler. buildingId: %d', $this->colony->getId(), $this->buildingId));
            }

            $researchedArray = $this->researchedRepository->getFinishedListByUser($this->colony->getUser()->getId());

            array_walk(
                $fields,
                function (PlanetFieldInterface $field) use ($building, $researchedArray): void {
                    if (
                        $field->getTerraformingId() === null &&
                        $building->getBuildableFields()->containsKey((int) $field->getFieldType())
                    ) {

                        //PlanetFieldTypeBuildingInterface
                        $fieldBuilding = $building->getBuildableFields()->get((int) $field->getFieldType());

                        $researchId = $fieldBuilding->getResearchId();
                        if ($researchId == null || $this->isResearched($researchId, $researchedArray)) {
                            $field->setBuildMode(true);
                        }
                    }
                }
            );
        }

        return $fields;
    }

    private function isResearched(int $researchId, array $researched): bool
    {
        foreach ($researched as $research) {
            if ($research->getResearchId() == $researchId) {
                return true;
            }
        }

        return false;
    }

    public function getSurfaceTileCssClass(): string
    {
        if ($this->colony->getPlanetType()->getIsMoon()) {
            return 'moonSurfaceTiles';
        }
        return 'planetSurfaceTiles';
    }

    public function getEpsBoxTitleString(): string
    {
        $energyProduction = $this->colony->getEpsProduction();

        $forecast = $this->colony->getEps() + $energyProduction;

        if ($this->colony->getEps() + $energyProduction < 0) {
            $forecast = 0;
        }
        if ($this->colony->getEps() + $energyProduction > $this->colony->getMaxEps()) {
            $forecast = $this->colony->getMaxEps();
        }
        if ($energyProduction > 0) {
            $energyProduction = sprintf('+%d', $energyProduction);
        }

        return sprintf(
            _('Energie: %d/%d (%s/Runde = %d)'),
            $this->colony->getEps(),
            $this->colony->getMaxEps(),
            $energyProduction,
            $forecast
        );
    }

    public function getShieldBoxTitleString(): string
    {
        return sprintf(
            _('Schildstärke: %d/%d'),
            $this->colony->getShields(),
            $this->colony->getMaxShields()
        );
    }

    public function getPositiveEffectPrimaryDescription(): string
    {
        switch ($this->colony->getUser()->getFactionId()) {
            case FactionEnum::FACTION_FEDERATION:
                return _('Zufriedenheit');
            case FactionEnum::FACTION_ROMULAN:
                return _('Loyalität');
            case FactionEnum::FACTION_KLINGON:
                return _('Ehre');
            case FactionEnum::FACTION_CARDASSIAN:
                return _('Stolz');
            case FactionEnum::FACTION_FERENGI:
                return _('Wohlstand');
        }
        return '';
    }

    public function getPositiveEffectSecondaryDescription(): string
    {
        switch ($this->colony->getUser()->getFactionId()) {
            case FactionEnum::FACTION_FEDERATION:
                return _('Bildung');
            case FactionEnum::FACTION_ROMULAN:
                return _('Imperiales Gedankengut');
            case FactionEnum::FACTION_KLINGON:
                return _('Kampftraining');
            case FactionEnum::FACTION_CARDASSIAN:
                return _('Patriotismus');
            case FactionEnum::FACTION_FERENGI:
                return _('Profitgier');
        }
        return '';
    }

    public function getNegativeEffectDescription(): string
    {
        switch ($this->colony->getUser()->getFactionId()) {
            case FactionEnum::FACTION_FEDERATION:
                return _('Bevölkerungsdichte');
            case FactionEnum::FACTION_ROMULAN:
                return _('Bevölkerungsdichte');
            case FactionEnum::FACTION_KLINGON:
                return _('Bevölkerungsdichte');
            case FactionEnum::FACTION_CARDASSIAN:
                return _('Bevölkerungsdichte');
            case FactionEnum::FACTION_FERENGI:
                return _('Bevölkerungsdichte');
        }
        return '';
    }

    public function getStorageSumPercent(): float
    {
        $maxStorage = $this->colony->getMaxStorage();

        if ($maxStorage === 0) {
            return 0;
        }

        return round(100 / $maxStorage * $this->colony->getStorageSum(), 2);
    }

    public function updateSurface(): array
    {
        if ($this->colony->getMask() === null) {
            $generator = new PlanetGenerator($this->loggerUtil);

            $surface = $generator->generateColony(
                $this->colony->getColonyClass(),
                $this->colony->getSystem()->getBonusFieldAmount()
            );
            $this->colony->setMask(base64_encode(serialize($surface)));

            $this->colonyRepository->save($this->colony);
        }

        $fields = $this->colony->getPlanetFields()->toArray();

        $surface = unserialize(base64_decode($this->colony->getMask()));
        foreach ($surface as $fieldId => $type) {
            if (!array_key_exists($fieldId, $fields)) {
                $newField = $this->planetFieldRepository->prototype();
                $fields[$fieldId] = $newField;
                $fields[$fieldId]->setColony($this->colony);
                $fields[$fieldId]->setFieldId($fieldId);
                $this->colony->getPlanetFields()->set($fieldId,  $newField);
            }
            $fields[$fieldId]->setBuilding(null);
            $fields[$fieldId]->setIntegrity(0);
            $fields[$fieldId]->setFieldType((int) $type);
            $fields[$fieldId]->setActive(0);

            $this->planetFieldRepository->save($fields[$fieldId]);
        }

        $this->entityManager->flush();

        return $fields;
    }

    public function getProductionSumClass(): string
    {
        if ($this->colony->getProductionSum() < 0) {
            return 'negative';
        }
        if ($this->colony->getProductionSum() > 0) {
            return 'positive';
        }
        return '';
    }

    public function hasShipyard(): bool
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $this->colony->getId(),
            BuildingFunctionTypeEnum::getShipyardOptions(),
            [0, 1]
        ) > 0;
    }

    public function hasModuleFab(): bool
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $this->colony->getId(),
            BuildingFunctionTypeEnum::getModuleFabOptions(),
            [0, 1]
        ) > 0;
    }

    public function hasAirfield(): bool
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $this->colony->getId(),
            [BuildingEnum::BUILDING_FUNCTION_AIRFIELD],
            [0, 1]
        ) > 0;
    }

    public function getDayNightState(): string
    {
        // @todo implement
        $hour = date('G');

        if ($hour > 7 && $hour < 19) {
            return 't';
        }
        return 'n';
    }
}
