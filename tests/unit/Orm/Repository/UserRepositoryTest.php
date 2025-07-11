<?php

declare(strict_types=1);

namespace Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery\MockInterface;
use Override;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepository;
use Stu\StuTestCase;

class UserRepositoryTest extends StuTestCase
{
    private EntityManagerInterface&MockInterface $entityManager;

    private MockInterface&ClassMetadata $classMetaData;

    private UserRepository $subject;

    #[Override]
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
            ->with(User::class, UserEnum::USER_NOONE, null, null)
            ->once()
            ->andReturn($user);

        static::assertSame(
            $user,
            $this->subject->getFallbackUser()
        );
    }
}
