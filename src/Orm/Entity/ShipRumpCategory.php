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
use Stu\Orm\Repository\ShipRumpCategoryRepository;

#[Table(name: 'stu_rumps_categories')]
#[Entity(repositoryClass: ShipRumpCategoryRepository::class)]
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

    #[Override]
    public function getId(): int
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

    //@deprecated
    #[Override]
    public function getPoints(): int
    {
        return $this->points;
    }

    #[Override]
    public function setPoints(int $points): ShipRumpCategoryInterface
    {
        $this->points = $points;

        return $this;
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
}
