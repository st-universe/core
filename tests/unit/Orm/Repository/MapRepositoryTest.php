<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\TestWith;
use Stu\Component\Map\MapEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class MapRepositoryTest extends StuTestCase
{
    private EntityManager $entityManager;
    private MapRepository $subject;
    private Layer $layer;

    #[\Override]
    protected function setUp(): void
    {
        $configuration = ORMSetup::createAttributeMetadataConfig([], true);
        $configuration->enableNativeLazyObjects(true);
        $configuration->setNamingStrategy(new UnderscoreNamingStrategy());
        $this->entityManager = new EntityManager(
            DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]),
            $configuration
        );
        $schema = new SchemaTool($this->entityManager)->getSchemaFromMetadata(array_map(
            $this->entityManager->getClassMetadata(...),
            [Layer::class, Location::class, Map::class, MapFieldType::class, Spacecraft::class, Ship::class]
        ));
        foreach ($schema->getTables() as $table) {
            foreach ($table->getForeignKeys() as $foreignKey) {
                $table->removeForeignKey($foreignKey->getName());
            }
        }
        foreach ($schema->toSql($this->entityManager->getConnection()->getDatabasePlatform()) as $sql) {
            $this->entityManager->getConnection()->executeStatement($sql);
        }
        $this->subject = $this->entityManager->getRepository(Map::class);
        $this->layer = $this->createLayer();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager->getConnection()->close();
        parent::tearDown();
    }

    #[TestWith(['impassable'])]
    #[TestWith(['damage'])]
    #[TestWith(['no_pirates'])]
    #[TestWith(['central'])]
    #[TestWith(['peripheral'])]
    #[TestWith(['occupied'])]
    #[TestWith(['other_layer'])]
    public function testRandomSpawnExcludesForbiddenLocations(string $reason): void
    {
        $type = new MapFieldType()->setPassable(true);
        $forbidden = $this->createMap($type);
        match ($reason) {
            'impassable' => $type->setPassable(false),
            'damage' => $type->setSpecialDamage(1),
            'no_pirates' => $type->setEffects([FieldTypeEffectEnum::NO_PIRATES]),
            'central' => $forbidden->setAdminRegionId(MapEnum::ADMIN_REGION_SUPERPOWER_CENTRAL),
            'peripheral' => $forbidden->setAdminRegionId(MapEnum::ADMIN_REGION_SUPERPOWER_PERIPHERAL),
            'occupied' => $this->occupy($forbidden),
            'other_layer' => $this->entityManager->getClassMetadata(Location::class)->setFieldValue($forbidden, 'layer', $this->createLayer())
        };
        if ($forbidden->getAdminRegionId() !== null) {
            $this->setAdminRegion($forbidden, $forbidden->getAdminRegionId());
        }
        $this->entityManager->flush();

        self::assertNull($this->subject->getRandomPirateSpawnLocation($this->layer));

        $allowed = $this->createMap(new MapFieldType()->setPassable(true));
        $this->entityManager->flush();

        self::assertSame($allowed, $this->subject->getRandomPirateSpawnLocation($this->layer));
    }

    public function testEmptyLayerHasNoSpawnLocation(): void
    {
        $this->entityManager->flush();

        self::assertNull($this->subject->getRandomPirateSpawnLocation($this->layer));
    }

    public function testUnrelatedEffectsAndRegionsAllowSpawning(): void
    {
        $allowed = $this->createMap(new MapFieldType()->setPassable(true)->setEffects([FieldTypeEffectEnum::NO_ANOMALIES]));
        $this->setAdminRegion($allowed, 1);
        $this->entityManager->flush();

        self::assertSame($allowed, $this->subject->getRandomPirateSpawnLocation($this->layer));
    }

    private function createLayer(): Layer
    {
        $layer = new Layer();
        $metadata = $this->entityManager->getClassMetadata(Layer::class);
        foreach (['name' => 'Test', 'width' => 10, 'height' => 10, 'is_hidden' => false] as $field => $value) {
            $metadata->setFieldValue($layer, $field, $value);
        }
        $this->entityManager->persist($layer);
        return $layer;
    }

    private function createMap(MapFieldType $fieldType): Map
    {
        $map = new Map();
        $map->setFieldType($fieldType);
        $metadata = $this->entityManager->getClassMetadata(Location::class);
        $metadata->setFieldValue($map, 'layer', $this->layer);
        $metadata->setFieldValue($map, 'cx', 1);
        $metadata->setFieldValue($map, 'cy', 1);
        $this->entityManager->persist($fieldType);
        $this->entityManager->persist($map);
        return $map;
    }

    private function occupy(Map $map): void
    {
        $ship = new Ship();
        $ship->setLocation($map);
        $ship->setUser($this->entityManager->getReference(User::class, 17));
        $ship->setRump($this->entityManager->getReference(SpacecraftRump::class, 1));
        $this->entityManager->persist($ship);
    }

    private function setAdminRegion(Map $map, int $regionId): void
    {
        $map->setAdminRegionId($regionId);
        $this->entityManager->getClassMetadata(Map::class)->setFieldValue(
            $map,
            'administratedRegion',
            $this->entityManager->getReference(MapRegion::class, $regionId)
        );
    }
}
