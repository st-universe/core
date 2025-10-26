<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery\MockInterface;
use Stu\Orm\Entity\FlightSignature;
use Stu\StuTestCase;

class FlightSignatureRepositoryTest extends StuTestCase
{
    private EntityManagerInterface&MockInterface $entityManager;

    private MockInterface&ClassMetadata $classMetaData;

    private FlightSignatureRepository $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->classMetaData = $this->mock(ClassMetadata::class);

        $this->classMetaData->name = FlightSignature::class;

        $this->subject = new FlightSignatureRepository(
            $this->entityManager,
            $this->classMetaData
        );
    }

    public function testPrototypeReturnsInstance(): void
    {
        static::assertInstanceOf(
            FlightSignature::class,
            $this->subject->prototype()
        );
    }

    public function testSaveSaves(): void
    {
        $entity = $this->mock(FlightSignature::class);

        $this->entityManager->shouldReceive('persist')
            ->with($entity)
            ->once();

        $this->subject->save($entity);
    }
}
