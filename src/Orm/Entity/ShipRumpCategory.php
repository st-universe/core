<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Orm\Repository\ShipRumpCategoryRepository;

#[Table(name: 'stu_rumps_categories')]
#[Entity(repositoryClass: ShipRumpCategoryRepository::class)]
class ShipRumpCategory implements ShipRumpCategoryInterface
{
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    #[Column(type: 'integer', enumType: SpacecraftRumpCategoryEnum::class)]
    private SpacecraftRumpCategoryEnum $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer', nullable: true)]
    private ?int $database_id = 0;

    #[Column(type: 'string', enumType: SpacecraftTypeEnum::class)]
    private SpacecraftTypeEnum $type = SpacecraftTypeEnum::SHIP;

    #[ManyToOne(targetEntity: 'DatabaseEntry')]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntryInterface $databaseEntry = null;

    #[Override]
    public function getId(): SpacecraftRumpCategoryEnum
    {
        return $this->id;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function setName(string $name): ShipRumpCategoryInterface
    {
        $this->name = $name;

        return $this;
    }

    #[Override]
    public function getDatabaseId(): int
    {
        return $this->database_id;
    }

    #[Override]
    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    #[Override]
    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): ShipRumpCategoryInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    #[Override]
    public function getType(): SpacecraftTypeEnum
    {
        return $this->type;
    }
}
