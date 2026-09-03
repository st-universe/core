<?php

declare(strict_types=1);

namespace Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery\MockInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepository;
use Stu\StuTestCase;

class UserRepositoryTest extends StuTestCase
{
    private EntityManagerInterface&MockInterface $entityManager;

    private MockInterface&ClassMetadata $classMetaData;

    private UserRepository $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->classMetaData = $this->mock(ClassMetadata::class);

        $this->classMetaData->name = User::class;

        $this->subject = new UserRepository(
            $this->entityManager,
            $this->classMetaData
        );
    }

    public function testGetFallbackUserReturnsItem(): void
    {
        $user = $this->mock(User::class);

        $this->entityManager->shouldReceive('find')
            ->with(User::class, UserConstants::USER_NOONE, null, null)
            ->once()
            ->andReturn($user);

        self::assertSame(
            $user,
            $this->subject->getFallbackUser()
        );
    }

    public function testLockUsersForUpdateSortsAndDeduplicatesUserIds(): void
    {
        $query = $this->mock(\Doctrine\ORM\Query::class);

        $this->entityManager->shouldReceive('createQuery')
            ->with('SELECT u FROM Stu\\Orm\\Entity\\User u WHERE u.id IN (:userIds) ORDER BY u.id ASC')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('setParameter')
            ->with('userIds', [7, 12, 42])
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('setLockMode')
            ->with(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE)
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('getResult')
            ->once()
            ->andReturn([]);

        $this->subject->lockUsersForUpdate([42, 7, 42, 12]);
    }

    public function testLockAllUsersForUpdateLocksUsersInOrder(): void
    {
        $query = $this->mock(\Doctrine\ORM\Query::class);

        $this->entityManager->shouldReceive('createQuery')
            ->with("SELECT u.id FROM Stu\\Orm\\Entity\\User u WHERE u.id >= :firstNpcUserId ORDER BY u.id ASC")
            ->once()
            ->andReturn($query);
        $query->shouldReceive('setParameter')
            ->with('firstNpcUserId', UserConstants::USER_FIRST_NPC)
            ->once()
            ->andReturnSelf();
        $query->shouldReceive('setLockMode')
            ->with(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE)
            ->once()
            ->andReturnSelf();
        $query->shouldReceive('getResult')
            ->once()
            ->andReturn([]);

        $this->subject->lockAllUsersForUpdate();
    }
}
