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
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Orm\Repository\ShipRumpCategoryRepository;

#[Table(name: 'stu_rumps_categories')]
#[Entity(repositoryClass: ShipRumpCategoryRepository::class)]
class ShipRumpCategory
{
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    #[Column(type: 'integer', enumType: SpacecraftRumpCategoryEnum::class)]
    private SpacecraftRumpCategoryEnum $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'string', enumType: SpacecraftTypeEnum::class)]
    private SpacecraftTypeEnum $type = SpacecraftTypeEnum::SHIP;

    #[ManyToOne(targetEntity: DatabaseEntry::class)]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntry $databaseEntry = null;

    public function getId(): SpacecraftRumpCategoryEnum
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ShipRumpCategory
    {
        $this->name = $name;

        return $this;
    }

    public function getDatabaseEntry(): ?DatabaseEntry
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntry $databaseEntry): ShipRumpCategory
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }

    public function getType(): SpacecraftTypeEnum
    {
        return $this->type;
    }
}
