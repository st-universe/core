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

#[Table(name: 'stu_rumps_categories')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ShipRumpCategoryRepository')]
class ShipRumpCategory implements ShipRumpCategoryInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'integer', nullable: true)]
    private ?int $database_id = 0;

    #[Column(type: 'integer')]
    private int $points = 0;

    #[ManyToOne(targetEntity: 'DatabaseEntry')]
    #[JoinColumn(name: 'database_id', referencedColumnName: 'id')]
    private ?DatabaseEntryInterface $databaseEntry = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ShipRumpCategoryInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDatabaseId(): int
    {
        return $this->database_id;
    }

    public function setDatabaseId(int $databaseId): ShipRumpCategoryInterface
    {
        $this->database_id = $databaseId;

        return $this;
    }

    //@deprecated
    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): ShipRumpCategoryInterface
    {
        $this->points = $points;

        return $this;
    }

    public function getDatabaseEntry(): ?DatabaseEntryInterface
    {
        return $this->databaseEntry;
    }

    public function setDatabaseEntry(?DatabaseEntryInterface $databaseEntry): ShipRumpCategoryInterface
    {
        $this->databaseEntry = $databaseEntry;

        return $this;
    }
}
