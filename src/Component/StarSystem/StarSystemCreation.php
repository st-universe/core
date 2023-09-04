<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

use RuntimeException;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\MapFieldTypeInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\MassCenterTypeInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemTypeInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use Stu\StarsystemGenerator\StarsystemGeneratorInterface;
use Stu\StarsystemGenerator\SystemMapDataInterface;

//TODO unit tests
final class StarSystemCreation implements StarSystemCreationInterface
{
    private StarSystemRepositoryInterface $starSystemRepository;

    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private MapFieldTypeRepositoryInterface $mapFieldTypeRepository;

    private StarsystemGeneratorInterface $starsystemGenerator;

    private LoggerUtilInterface $loggerUtil;

    /** @var array<int, MapFieldTypeInterface> */
    private array $fieldTypeCache = [];

    public function __construct(
        StarSystemRepositoryInterface $starSystemRepository,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        StarsystemGeneratorInterface $starsystemGenerator,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->starSystemRepository = $starSystemRepository;
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
        $this->starsystemGenerator = $starsystemGenerator;

        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function recreateStarSystem(MapInterface $map): void
    {
        $this->loggerUtil->init('SysGen', LoggerEnum::LEVEL_ERROR);

        $this->loggerUtil->log(sprintf('recreating for map: %s', $map->getSectorString()));

        $systemType = $map->getStarSystemType();

        if ($systemType === null) {
            throw new RuntimeException('foo');
        }

        $this->loggerUtil->log(sprintf('systemType: %d, isGenerateable: %s', $systemType->getId(), $systemType->getIsGenerateable() ? 'true' : 'false'));

        if (
            $systemType->getIsGenerateable() === null
            || $systemType->getIsGenerateable() === false
        ) {
            $this->loggerUtil->log('not');
            //return;
        }

        $this->loggerUtil->log('A');

        $firstMassCenterType = $systemType->getFirstMassCenterType();
        $secondMassCenterType = $systemType->getSecondMassCenterType();

        if ($firstMassCenterType === null) {
            throw new RuntimeException('foo');
        }

        $systemMapData = $this->starsystemGenerator->generate(
            $systemType->getId(),
            $this->getMassCenterFields($firstMassCenterType),
            $secondMassCenterType === null ? null : $this->getMassCenterFields($secondMassCenterType)
        );

        $starSystem = $this->getStarSystem($map);
        $this->initializeStarSystem($systemType, $map, $starSystem, $systemMapData);
        $this->starSystemRepository->save($starSystem);

        $this->loggerUtil->log('B');
    }

    private function initializeStarSystem(
        StarSystemTypeInterface $systemType,
        MapInterface $map,
        StarSystemInterface $starSystem,
        SystemMapDataInterface $mapData
    ): void {
        $starSystem->setCx($map->getCx());
        $starSystem->setCy($map->getCy());
        $starSystem->setType($systemType);
        $starSystem->setName($this->getRandomSystemName());
        $starSystem->setMaxX($mapData->getWidth());
        $starSystem->setMaxY($mapData->getHeight());
        //TODO bonus felder
        //database entry erstellen
        $this->createSystemMapEntries($starSystem, $mapData);
    }

    private function createSystemMapEntries(
        StarSystemInterface $starSystem,
        SystemMapDataInterface $mapData
    ): void {
        $fieldData = $mapData->getFieldData();

        for ($y = 1; $y <= $mapData->getHeight(); $y++) {
            for ($x = 1; $x <= $mapData->getWidth(); $x++) {
                $index = $x + ($y - 1) * $mapData->getWidth();

                $this->createSystemMap($x, $y, $fieldData[$index], $starSystem);
            }
        }
    }

    private function createSystemMap(int $x, int $y, int $fieldId, StarSystemInterface $starSystem): void
    {
        $systemMap = $this->starSystemMapRepository->prototype();
        $systemMap->setSx($x);
        $systemMap->setSy($y);
        $systemMap->setSystem($starSystem);
        $systemMap->setFieldType($this->getFieldType($fieldId));

        $this->starSystemMapRepository->save($systemMap);

        $starSystem->getFields()->add($systemMap);
    }

    private function getFieldType(int $fieldId): MapFieldTypeInterface
    {
        if (!array_key_exists($fieldId, $this->fieldTypeCache)) {
            $fieldType = $this->mapFieldTypeRepository->find($fieldId === 0 ? 1 : $fieldId);

            if ($fieldType === null) {
                throw new RuntimeException(sprintf('fieldId %d does not exist', $fieldId));
            }
            $this->fieldTypeCache[$fieldId] = $fieldType;
        }

        return $this->fieldTypeCache[$fieldId];
    }

    private function getRandomSystemName(): string
    {
        return $this->starSystemRepository->getRandomFreeSystemName()->getName();
    }

    private function getStarSystem(MapInterface $map): StarSystemInterface
    {
        $starSystem = $map->getSystem();
        if ($starSystem === null) {
            $starSystem = $this->starSystemRepository->prototype();
            $map->setSystem($starSystem);
            $this->mapRepository->save($map);
        } else {
            $this->starSystemMapRepository->truncateByStarSystem($starSystem);
            $starSystem->getFields()->clear();
        }

        return $starSystem;
    }

    /**
     * @return array<int, int>
     */
    private function getMassCenterFields(MassCenterTypeInterface $massCenterType): array
    {
        $result = [];

        $firstId = $massCenterType->getFirstFieldType()->getId();

        for ($i = 0; $i < (int)pow($massCenterType->getSize(), 2); $i++) {
            $result[] = $firstId + $i;
        }

        return $result;
    }
}
