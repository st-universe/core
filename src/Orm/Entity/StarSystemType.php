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
use RuntimeException;

#[Table(name: 'stu_system_types')]
#[Index(name: 'starsystem_mass_center_1_idx', columns: ['first_mass_center_id'])]
#[Index(name: 'starsystem_mass_center_2_idx', columns: ['second_mass_center_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\StarSystemTypeRepository')]
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

    #[OneToOne(targetEntity: 'DatabaseEntry')]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntryInterface $databaseEntry = null;

    #[ManyToOne(targetEntity: 'MassCenterType')]
    #[JoinColumn(name: 'first_mass_center_id', referencedColumnName: 'id')]
    private ?MassCenterTypeInterface $firstMassCenterType = null;

    #[ManyToOne(targetEntity: 'MassCenterType')]
    #[JoinColumn(name: 'second_mass_center_id', referencedColumnName: 'id')]
    private ?MassCenterTypeInterface $secondMassCenterType = null;

    public function getId(): int
    {
        return $this->id;
    }

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

    public function setDescription(string $description): StarSystemTypeInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getDatabaseEntryId(): ?int
    {
        return $this->database_id;
    }

    public function setDatabaseEntryId(?int $databaseEntryId): StarSystemTypeInterface
    {
        $this->database_id = $databaseEntryId;

        return $this;
    }

    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): StarSystemTypeInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    public function getFirstMassCenterType(): ?MassCenterTypeInterface
    {
        return $this->firstMassCenterType;
    }

    public function getSecondMassCenterType(): ?MassCenterTypeInterface
    {
        return $this->secondMassCenterType;
    }

    public function getIsGenerateable(): ?bool
    {
        return $this->is_generateable;
    }
}
