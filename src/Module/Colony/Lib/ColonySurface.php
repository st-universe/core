<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyPopulationCalculatorInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Building\BuildingFunctionTypeEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\PlanetGenerator\Exception\PlanetGeneratorException;
use Stu\PlanetGenerator\PlanetGeneratorInterface;

/**
 * Provides access to several colony surface related methods
 */
final class ColonySurface implements ColonySurfaceInterface
{
    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private BuildingRepositoryInterface $buildingRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private PlanetGeneratorInterface $planetGenerator;

    private EntityManagerInterface $entityManager;

    private LoggerUtilInterface $loggerUtil;

    private ColonyInterface $colony;

    private ?int $buildingId;

    private bool $showUnderground;

    private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ?int $energyProduction = null;

    private ?ColonyPopulationCalculatorInterface $colonyPopulationCalculator = null;

    /** @var array<ColonyProduction>|null */
    private ?array $production = null;

    public function __construct(
        ColonyLibFactoryInterface $colonyLibFactory,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository,
        ColonyRepositoryInterface $colonyRepository,
        ResearchedRepositoryInterface $researchedRepository,
        PlanetGeneratorInterface $planetGenerator,
        EntityManagerInterface $entityManager,
        LoggerUtilInterface $loggerUtil,
        PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        ColonyInterface $colony,
        ?int $buildingId = null,
        bool $showUnderground = true
    ) {
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colony = $colony;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
        $this->buildingId = $buildingId;
        $this->colonyRepository = $colonyRepository;
        $this->researchedRepository = $researchedRepository;
        $this->planetGenerator = $planetGenerator;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtil;
        $this->showUnderground = $showUnderground;
        $this->planetFieldTypeRetriever = $planetFieldTypeRetriever;
    }

    public function getSurface(): array
    {
        try {
            $this->updateSurface();
        } catch (PlanetGeneratorException $planetGeneratorException) {
            return $this->colony->getPlanetFields()->toArray();
        }

        $fields = $this->colony->getPlanetFields()->toArray();

        if (!$this->showUnderground) {
            $fields = array_filter(
                $fields,
                fn (PlanetFieldInterface $field): bool => !$this->planetFieldTypeRetriever->isUndergroundField($field)
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
                        $building->getBuildableFields()->containsKey($field->getFieldType())
                    ) {
                        //PlanetFieldTypeBuildingInterface
                        $fieldBuilding = $building->getBuildableFields()->get($field->getFieldType());

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

    public function getSurfaceTileStyle(): string
    {
        $width = $this->planetGenerator->loadColonyClassConfig($this->colony->getColonyClassId())['sizew'];
        $gridArray = [];
        for ($i = 0; $i < $width; $i++) {
            $gridArray[] = '43px';
        }

        return sprintf('display: grid; grid-template-columns: %s;', implode(' ', $gridArray));
    }

    public function getEpsBoxTitleString(): string
    {
        $energyProduction = $this->getEnergyProduction();

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
            'Schildstärke: %d/%d',
            $this->colony->getShields(),
            $this->planetFieldRepository->getMaxShieldsOfColony($this->colony->getId())
        );
    }

    public function getStorageSumPercent(): float
    {
        $maxStorage = $this->colony->getMaxStorage();

        if ($maxStorage === 0) {
            return 0;
        }

        return round(100 / $maxStorage * $this->colony->getStorageSum(), 2);
    }

    public function updateSurface(): void
    {
        if (!$this->colony->isFree()) {
            return;
        }

        $mask = $this->colony->getMask();

        if ($mask === null) {
            $planetConfig = $this->planetGenerator->generateColony(
                $this->colony->getColonyClassId(),
                $this->colony->getSystem()->getBonusFieldAmount()
            );

            $mask = base64_encode(serialize($planetConfig->getFieldArray()));

            $this->colony->setMask($mask);
            $this->colony->setSurfaceWidth($planetConfig->getSurfaceWidth());

            $this->colonyRepository->save($this->colony);
        }

        $fields = $this->colony->getPlanetFields()->toArray();

        $surface = unserialize(base64_decode($mask));
        foreach ($surface as $fieldId => $type) {
            if (!array_key_exists($fieldId, $fields)) {
                $newField = $this->planetFieldRepository->prototype();
                $fields[$fieldId] = $newField;
                $fields[$fieldId]->setColony($this->colony);
                $fields[$fieldId]->setFieldId($fieldId);
                $this->colony->getPlanetFields()->set($fieldId, $newField);
            }

            $fields[$fieldId]->setBuilding(null);
            $fields[$fieldId]->setIntegrity(0);
            $fields[$fieldId]->setFieldType((int) $type);
            $fields[$fieldId]->setActive(0);

            $this->planetFieldRepository->save($fields[$fieldId]);
        }

        $this->entityManager->flush();
    }

    public function getUserDepositMinings(): array
    {
        $production = $this->getProduction();

        $result = [];

        foreach ($this->colony->getDepositMinings() as $deposit) {
            if ($deposit->getUser() === $this->colony->getUser()) {
                $prod = $production[$deposit->getCommodity()->getId()] ?? null;

                $result[$deposit->getCommodity()->getId()] = [
                    'deposit' => $deposit,
                    'currentlyMined' => $prod === null ? 0 : $prod->getProduction()
                ];
            }
        }

        return $result;
    }

    public function getEnergyProduction(): int
    {
        if ($this->energyProduction === null) {
            $this->energyProduction = $this->planetFieldRepository->getEnergyProductionByColony(
                $this->colony->getId()
            );
        }

        return $this->energyProduction;
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

    public function getPopulation(): ColonyPopulationCalculatorInterface
    {
        if ($this->colonyPopulationCalculator === null) {
            $this->colonyPopulationCalculator = $this->colonyLibFactory->createColonyPopulationCalculator(
                $this->colony,
                $this->getProduction()
            );
        }

        return $this->colonyPopulationCalculator;
    }

    /**
     * @return array<ColonyProduction>
     */
    private function getProduction(): array
    {
        if ($this->production === null) {
            $this->production = $this->colonyLibFactory->createColonyCommodityProduction($this->colony)->getProduction();
        }

        return $this->production;
    }
}
