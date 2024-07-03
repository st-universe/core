<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Building\BuildingEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Building\BuildingFunctionTypeEnum;
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

    private PlanetFieldHostInterface $host;

    private ?int $buildingId;

    private bool $showUnderground;

    private PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingRepositoryInterface $buildingRepository,
        ColonyRepositoryInterface $colonyRepository,
        ResearchedRepositoryInterface $researchedRepository,
        PlanetGeneratorInterface $planetGenerator,
        EntityManagerInterface $entityManager,
        PlanetFieldTypeRetrieverInterface $planetFieldTypeRetriever,
        PlanetFieldHostInterface $host,
        ?int $buildingId,
        bool $showUnderground
    ) {
        $this->host = $host;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingRepository = $buildingRepository;
        $this->buildingId = $buildingId;
        $this->colonyRepository = $colonyRepository;
        $this->researchedRepository = $researchedRepository;
        $this->planetGenerator = $planetGenerator;
        $this->entityManager = $entityManager;
        $this->showUnderground = $showUnderground;
        $this->planetFieldTypeRetriever = $planetFieldTypeRetriever;
    }

    public function getSurface(): array
    {
        try {
            $this->updateSurface();
        } catch (PlanetGeneratorException $e) {
            return $this->host->getPlanetFields()->toArray();
        }

        $fields = $this->host->getPlanetFields()->toArray();

        if (!$this->showUnderground) {
            $fields = array_filter(
                $fields,
                fn (PlanetFieldInterface $field): bool => !$this->planetFieldTypeRetriever->isUndergroundField($field)
            );
        }

        if ($this->buildingId !== null) {
            $building = $this->buildingRepository->find($this->buildingId);
            $user = $this->host->getUser();

            $researchedArray = $this->researchedRepository->getFinishedListByUser($user->getId());

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
        $width = $this->planetGenerator->loadColonyClassConfig($this->host->getColonyClass()->getId())['sizew'];
        $gridArray = [];
        for ($i = 0; $i < $width; $i++) {
            $gridArray[] = '43px';
        }

        return sprintf('display: grid; grid-template-columns: %s;', implode(' ', $gridArray));
    }

    public function updateSurface(): void
    {
        $host = $this->host;
        if (!$host instanceof ColonyInterface) {
            return;
        }
        if (!$host->isFree()) {
            return;
        }

        $mask = $host->getMask();

        if ($mask === null) {
            $planetConfig = $this->planetGenerator->generateColony(
                $host->getColonyClassId(),
                $host->getSystem()->getBonusFieldAmount()
            );

            $mask = base64_encode(serialize($planetConfig->getFieldArray()));

            $host->setMask($mask);
            $host->setSurfaceWidth($planetConfig->getSurfaceWidth());

            $this->colonyRepository->save($host);
        }

        $fields = $host->getPlanetFields()->toArray();

        $surface = unserialize(base64_decode($mask));
        foreach ($surface as $fieldId => $type) {
            if (!array_key_exists($fieldId, $fields)) {
                $newField = $this->planetFieldRepository->prototype();
                $fields[$fieldId] = $newField;
                $fields[$fieldId]->setColony($host);
                $fields[$fieldId]->setFieldId($fieldId);
                $host->getPlanetFields()->set($fieldId, $newField);
            }

            $fields[$fieldId]->setBuilding(null);
            $fields[$fieldId]->setIntegrity(0);
            $fields[$fieldId]->setFieldType((int) $type);
            $fields[$fieldId]->setActive(0);

            $this->planetFieldRepository->save($fields[$fieldId]);
        }

        $this->entityManager->flush();
    }

    public function hasShipyard(): bool
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $this->host,
            BuildingFunctionTypeEnum::getShipyardOptions(),
            [0, 1]
        ) > 0;
    }

    public function hasAirfield(): bool
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $this->host,
            [BuildingEnum::BUILDING_FUNCTION_AIRFIELD],
            [0, 1]
        ) > 0;
    }
}
