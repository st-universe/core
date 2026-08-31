<?php

declare(strict_types=1);

namespace Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery\MockInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftRepository;
use Stu\StuTestCase;

class SpacecraftRepositoryTest extends StuTestCase
{
    private EntityManagerInterface|MockInterface $entityManager;

    private MockInterface|ClassMetadata $classMetaData;

    private SpacecraftRepository $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->classMetaData = $this->mock(ClassMetadata::class);

        $this->classMetaData->name = Spacecraft::class;

        $this->subject = new SpacecraftRepository(
            $this->entityManager,
            $this->classMetaData
        );
    }

    public function testGetUserIdsForSpacecraftsReturnsSortedDistinctUserIds(): void
    {
        $query = $this->mock(\Doctrine\ORM\Query::class);

        $this->entityManager->shouldReceive('createQuery')
            ->with("SELECT DISTINCT s.user_id
                FROM Stu\\Orm\\Entity\\Spacecraft s
                WHERE s.id IN (:spacecraftIds)")
            ->once()
            ->andReturn($query);

        $query->shouldReceive('setParameter')
            ->with('spacecraftIds', [13, 42, 99])
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('getResult')
            ->once()
            ->andReturn([
                ['user_id' => '42'],
                ['user_id' => '13'],
                ['user_id' => '42'],
                ['user_id' => '99'],
            ]);

        static::assertSame(
            [13, 42, 99],
            $this->subject->getUserIdsForSpacecrafts([99, 13, 42])
        );
    }
}
