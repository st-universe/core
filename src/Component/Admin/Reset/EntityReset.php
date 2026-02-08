<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset;

use Ahc\Cli\IO\Interactor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Generator;
use ReflectionClass;

class EntityReset
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function reset(Interactor $io): void
    {
        $count = 0;

        foreach ($this->entityClassesToTruncate() as $reflectionEntry) {
            $count++;
            $io->info(sprintf('  - removing all %s entities', $reflectionEntry->getShortName()), true);

            $this->entityManager->createQuery(
                sprintf(
                    'DELETE FROM %s',
                    $reflectionEntry->getClassName()
                )
            )->execute();
        }

        $io->info(sprintf('  - truncated %d tables', $count), true);
    }

    /**
     * @return Generator<EntityReflectionEntry>
     */
    private function entityClassesToTruncate(): Generator
    {
        /** @var array<EntityReflectionEntry> */
        $toTruncate = new ArrayCollection($this->entityManager->getMetadataFactory()->getAllMetadata())
            ->map(fn (ClassMetadata $metadata): EntityReflectionEntry => $this->createReflectionEntry($metadata->getName()))
            ->filter(fn (EntityReflectionEntry $entry): bool => $entry->hasTruncationAttribute())
            ->toArray();

        usort(
            $toTruncate,
            fn (EntityReflectionEntry $a, EntityReflectionEntry $b): int => $b->getPriority() <=> $a->getPriority()
        );

        foreach ($toTruncate as $entry) {
            yield $entry;
        }
    }

    /**
     * @param class-string $className
     */
    private function createReflectionEntry(string $className): EntityReflectionEntry
    {
        $reflClass = $this->createReflectionClass($className);

        return new EntityReflectionEntry(
            $className,
            $reflClass
        );
    }

    /**
     * @param class-string $className
     *
     * @return ReflectionClass<object>
     */
    public function createReflectionClass(string $className): ReflectionClass
    {
        return new ReflectionClass($className);
    }
}
