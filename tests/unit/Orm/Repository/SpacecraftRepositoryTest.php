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
        $query1 = $this->mock(\Doctrine\ORM\Query::class);
        $query2 = $this->mock(\Doctrine\ORM\Query::class);

        $this->entityManager->shouldReceive('createQuery')
            ->with("SELECT DISTINCT sp.user_id
                FROM Stu\\Orm\\Entity\\Spacecraft sp
                WHERE sp.id IN (:spacecraftIds)")
            ->once()
            ->andReturn($query1);
        $this->entityManager->shouldReceive('createQuery')
            ->with("SELECT DISTINCT st.user_id
                FROM Stu\\Orm\\Entity\\Ship sh
                JOIN sh.dockedTo st
                WHERE sh.id IN (:spacecraftIds)")
            ->once()
            ->andReturn($query2);

        $query1->shouldReceive('setParameter')
            ->with('spacecraftIds', [13, 42, 99])
            ->once()
            ->andReturnSelf();
        $query1->shouldReceive('getResult')
            ->once()
            ->andReturn([
                ['user_id' => '42'],
                ['user_id' => '13'],
                ['user_id' => '42'],
                ['user_id' => '99'],
            ]);

        $query2->shouldReceive('setParameter')
            ->with('spacecraftIds', [13, 42, 99])
            ->once()
            ->andReturnSelf();
        $query2->shouldReceive('getResult')
            ->once()
            ->andReturn([
                ['user_id' => '42'],
                ['user_id' => '17']
            ]);

        static::assertSame(
            [13, 17, 42, 99],
            $this->subject->getUserIdsForSpacecrafts([99, 13, 42])
        );
    }
}
