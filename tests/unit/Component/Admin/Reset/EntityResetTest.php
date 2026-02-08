<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset;

use Ahc\Cli\IO\Interactor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Mockery;
use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use ReflectionAttribute;
use ReflectionClass;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\StuTestCase;

class EntityResetTest extends StuTestCase
{
    private MockInterface&EntityManagerInterface $entityManager;
    private Interactor $interactor;

    private MockInterface&ReflectionClass $reflectionClassWithoutTruncateAttribute;
    private MockInterface&ReflectionClass $reflectionClassWithLowPriority;
    private MockInterface&ReflectionClass $reflectionClassWithHighPriority;

    private MockInterface&EntityReset $subject;

    #[\Override]
    public function setUp(): void
    {
        vfsStream::setup('tmpDir');

        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->interactor = new Interactor(null, vfsStream::url('tmpDir') . '/foo');

        $this->reflectionClassWithoutTruncateAttribute = $this->mock(ReflectionClass::class);
        $this->reflectionClassWithLowPriority = $this->mock(ReflectionClass::class);
        $this->reflectionClassWithHighPriority = $this->mock(ReflectionClass::class);

        $this->subject = Mockery::spy(new class (
            $this->reflectionClassWithoutTruncateAttribute,
            $this->reflectionClassWithLowPriority,
            $this->reflectionClassWithHighPriority,
            $this->entityManager
        ) extends EntityReset {
            public function __construct(
                private ReflectionClass $reflectionClassWithoutTruncateAttribute,
                private ReflectionClass $reflectionClassWithLowPriority,
                private ReflectionClass $reflectionClassWithHighPriority,
                EntityManagerInterface $entityManager
            ) {
                parent::__construct($entityManager);
            }
            #[\Override]
            public function createReflectionClass(string $className): ReflectionClass
            {
                return match ($className) {
                    'Stu\Orm\Entity\Station' => $this->reflectionClassWithoutTruncateAttribute,
                    'Stu\Orm\Entity\PirateWrath' => $this->reflectionClassWithLowPriority,
                    'Stu\Orm\Entity\OpenedAdventDoor' => $this->reflectionClassWithHighPriority,
                };
            }
        });
    }

    public function testReset(): void
    {
        $metadataWithoutTruncateAttribute = $this->mock(ClassMetadata::class);
        $metadataWithLowPriority = $this->mock(ClassMetadata::class);
        $metadataWithHighPriority = $this->mock(ClassMetadata::class);

        $reflectionAttributeWithLowPriority = $this->mock(ReflectionAttribute::class);
        $reflectionAttributeWithHighPriority = $this->mock(ReflectionAttribute::class);
        $truncateAttributeWithLowPriority = new TruncateOnGameReset(5);
        $truncateAttributeWithHighPriority = new TruncateOnGameReset(6);

        $firstQuery = $this->mock(Query::class);
        $secondQuery = $this->mock(Query::class);

        $this->entityManager->shouldReceive('getMetadataFactory->getAllMetadata')
            ->withNoArgs()
            ->once()
            ->andReturn([$metadataWithoutTruncateAttribute, $metadataWithLowPriority, $metadataWithHighPriority]);
        $this->entityManager->shouldReceive('createQuery')
            ->with('DELETE FROM Stu\Orm\Entity\OpenedAdventDoor')
            ->once()
            ->andReturn($firstQuery);
        $this->entityManager->shouldReceive('createQuery')
            ->with('DELETE FROM Stu\Orm\Entity\PirateWrath')
            ->once()
            ->andReturn($secondQuery);

        $firstQuery->shouldReceive('execute')
            ->withNoArgs()
            ->once();
        $secondQuery->shouldReceive('execute')
            ->withNoArgs()
            ->once();

        $metadataWithoutTruncateAttribute->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("Stu\Orm\Entity\Station");
        $metadataWithLowPriority->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("Stu\Orm\Entity\PirateWrath");
        $metadataWithHighPriority->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn("Stu\Orm\Entity\OpenedAdventDoor");


        $this->reflectionClassWithoutTruncateAttribute->shouldReceive('getAttributes')
            ->with(TruncateOnGameReset::class)
            ->andReturn([]);
        $this->reflectionClassWithLowPriority->shouldReceive('getAttributes')
            ->with(TruncateOnGameReset::class)
            ->andReturn([$reflectionAttributeWithLowPriority]);
        $this->reflectionClassWithHighPriority->shouldReceive('getAttributes')
            ->with(TruncateOnGameReset::class)
            ->andReturn([$reflectionAttributeWithHighPriority]);

        $this->reflectionClassWithLowPriority->shouldReceive('getShortName')
            ->withNoArgs()
            ->once()
            ->andReturn("PirateWrath");
        $this->reflectionClassWithHighPriority->shouldReceive('getShortName')
            ->withNoArgs()
            ->once()
            ->andReturn("OpenedAdventDoor");

        $reflectionAttributeWithLowPriority->shouldReceive('newInstance')
            ->withNoArgs()
            ->once()
            ->andReturn($truncateAttributeWithLowPriority);
        $reflectionAttributeWithHighPriority->shouldReceive('newInstance')
            ->withNoArgs()
            ->once()
            ->andReturn($truncateAttributeWithHighPriority);

        $this->subject->reset($this->interactor);
    }
}
