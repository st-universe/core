<?php

declare(strict_types=1);

namespace Stu;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftSystem;

abstract class ActionTestCase extends IntegrationTestCase
{
    #[\Override]
    public function setUp(): void
    {
        self::$isSchemaInitializationNeeded = true;
        parent::setUp();
        self::$isSchemaInitializationNeeded = true;
    }

    /**
     * @param class-string<T> $entityClass
     * @param array<string, mixed> $expectation
     */
    protected function assertEntity(int $id, string $entityClass, array $expectation): void
    {
        $entity = $this->getContainer()->get(EntityManagerInterface::class)
            ->getRepository($entityClass)
            ->find($id);

        $actual = $this->getTransformationClosure($entityClass)($entity);

        $this->assertEquals($expectation, $actual);
    }

    /**
     * @param class-string<T> $entityClass
     * @param array<string, mixed> $expectation
     */
    protected function assertEntities(string $entityClass, array $expectation): void
    {
        $entities = $this->getContainer()->get(EntityManagerInterface::class)
            ->getRepository($entityClass)
            ->findAll();

        $actual = array_map($this->getTransformationClosure($entityClass), $entities);

        $this->assertEquals($expectation, $actual);
    }

    /**
     * @param class-string<T> $entityClass
     */
    private function getTransformationClosure(string $entityClass): Closure
    {
        return match ($entityClass) {
            Spacecraft::class => fn(Spacecraft $s) => [
                'crewCount' => $s->getCrewCount()
            ],
            SpacecraftSystem::class => fn(SpacecraftSystem $ss) => [
                'data' => $ss->getData()
            ],
            GameTurn::class => fn(GameTurn $gt) => [
                'turn' => $gt->getTurn(),
                'startdate' => $gt->getStart(),
                'enddate' => $gt->getEnd(),
                'pirate_fleets' => $gt->getPirateFleets()
            ],
            default => throw new InvalidArgumentException(sprintf('no mapping found for %s', $entityClass))
        };
    }
}
