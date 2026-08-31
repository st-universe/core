<?php

declare(strict_types=1);

namespace Orm\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery\MockInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftRepository;
use Stu\StuTestCase;

class SpacecraftRepositoryTest extends StuTestCase
{
    private EntityManagerInterface&MockInterface $entityManager;

    private MockInterface&ClassMetadata $classMetaData;

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
        $connection = $this->mock(Connection::class);

        $this->entityManager->shouldReceive('getConnection')
            ->once()
            ->andReturn($connection);

        $connection->shouldReceive('fetchFirstColumn')
            ->with(
                'SELECT DISTINCT user_id FROM stu_spacecraft WHERE id IN (:spacecraftIds) ORDER BY user_id',
                ['spacecraftIds' => [13, 42, 99]]
            )
            ->once()
            ->andReturn(['42', '13', '42', '99']);

        static::assertSame(
            [13, 42, 99],
            $this->subject->getUserIdsForSpacecrafts([99, 13, 42])
        );
    }
}
