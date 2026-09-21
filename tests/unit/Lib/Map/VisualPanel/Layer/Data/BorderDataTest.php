<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\ObjectHydrator;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Query\ResultSetMapping;
use PHPUnit\Framework\Attributes\TestWith;
use Stu\StuTestCase;

class BorderDataTest extends StuTestCase
{
    #[TestWith(['impassable', true])]
    #[TestWith(['impassable', false])]
    #[TestWith(['cartographing', true])]
    #[TestWith(['cartographing', false])]
    public function testFlushDoesNotWriteHydratedBorderData(string $field, bool $value): void
    {
        $configuration = ORMSetup::createAttributeMetadataConfig([], true);
        $configuration->enableNativeLazyObjects(true);
        $configuration->setNamingStrategy(new UnderscoreNamingStrategy());

        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $entityManager = new EntityManager($connection, $configuration);

        $mapping = new ResultSetMapping();
        $mapping->addEntityResult(BorderData::class, 'd');
        $mapping->addFieldResult('d', 'x', 'x');
        $mapping->addFieldResult('d', 'y', 'y');
        $mapping->addFieldResult('d', $field, $field);
        $mapping->addFieldResult('d', 'complementary_color', 'complementary_color');

        $result = $this->mock(Result::class);
        $result->shouldReceive('fetchAssociative')
            ->twice()
            ->andReturn([
                'x' => 12,
                'y' => 34,
                $field => $value,
                'complementary_color' => '#123456'
            ], false);
        $result->shouldReceive('free')->once();

        $data = new ObjectHydrator($entityManager)->hydrateAll($result, $mapping);

        $this->assertCount(1, $data);
        $borderData = $data[0];
        $this->assertInstanceOf(BorderData::class, $borderData);
        $this->assertSame(12, $borderData->getPosX());
        $this->assertSame(34, $borderData->getPosY());
        $this->assertSame('#123456', $borderData->getComplementaryColor());
        $this->assertSame((string) $value, $field === 'impassable'
            ? $borderData->getImpassable()
            : $borderData->getCartographing());

        $entityManager->flush();

        $this->assertTrue($entityManager->isOpen());
        $this->assertSame([], $entityManager->getUnitOfWork()->getScheduledEntityUpdates());

        $entityManager->close();
        $connection->close();
    }
}
