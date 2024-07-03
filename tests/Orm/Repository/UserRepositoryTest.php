<?php

declare(strict_types=1);

namespace Orm\Repository;

use Override;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery\MockInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepository;
use Stu\StuTestCase;

class UserRepositoryTest extends StuTestCase
{
    /** @var EntityManagerInterface&MockInterface  */
    private MockInterface $entityManager;

    /** @var MockInterface&ClassMetadata */
    private MockInterface $classMetaData;

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
        $user = $this->mock(UserInterface::class);

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
