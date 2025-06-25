<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use RuntimeException;
use Stu\Orm\Repository\StarSystemTypeRepository;

#[Table(name: 'stu_system_types')]
#[Index(name: 'starsystem_mass_center_1_idx', columns: ['first_mass_center_id'])]
#[Index(name: 'starsystem_mass_center_2_idx', columns: ['second_mass_center_id'])]
#[Entity(repositoryClass: StarSystemTypeRepository::class)]
class StarSystemType implements StarSystemTypeInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $description = '';

    #[Column(type: 'integer', nullable: true)]
    private ?int $database_id = null;

    #[Column(type: 'boolean', nullable: true)]
    private ?bool $is_generateable = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $first_mass_center_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $second_mass_center_id = null;

    #[OneToOne(targetEntity: DatabaseEntry::class)]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntryInterface $databaseEntry = null;

    #[ManyToOne(targetEntity: MassCenterType::class)]
    #[JoinColumn(name: 'first_mass_center_id', referencedColumnName: 'id')]
    private ?MassCenterTypeInterface $firstMassCenterType = null;

    #[ManyToOne(targetEntity: MassCenterType::class)]
    #[JoinColumn(name: 'second_mass_center_id', referencedColumnName: 'id')]
    private ?MassCenterTypeInterface $secondMassCenterType = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getDescription(): string
    {
        $firstMassCenter = $this->getFirstMassCenterType();
        $secondMassCenter = $this->getSecondMassCenterType();

        if (
            $firstMassCenter === null
            && $secondMassCenter === null
        ) {
            return $this->description;
        }

        if ($firstMassCenter === null) {
            throw new RuntimeException('this is not allowed');
        }

        if ($secondMassCenter === null) {
            return $firstMassCenter->getDescription();
        }

        return sprintf(
            "Binärsystem %s-%s",
            $firstMassCenter->getDescription(),
            $secondMassCenter->getDescription()
        );
    }

    #[Override]
    public function setDescription(string $description): StarSystemTypeInterface
    {
        $this->description = $description;

        return $this;
    }

    #[Override]
    public function getDatabaseEntryId(): ?int
    {
        return $this->database_id;
    }

    #[Override]
    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    #[Override]
    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): StarSystemTypeInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    #[Override]
    public function getFirstMassCenterType(): ?MassCenterTypeInterface
    {
        return $this->firstMassCenterType;
    }

    #[Override]
    public function getSecondMassCenterType(): ?MassCenterTypeInterface
    {
        return $this->secondMassCenterType;
    }

    #[Override]
    public function getIsGenerateable(): ?bool
    {
        return $this->is_generateable;
    }
}
