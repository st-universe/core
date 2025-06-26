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
use Stu\Orm\Repository\DatabaseEntryRepository;

#[Table(name: 'stu_database_entrys')]
#[Entity(repositoryClass: DatabaseEntryRepository::class)]
class DatabaseEntry
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $description;

    #[Column(type: 'text')]
    private string $data;

    #[Column(type: 'integer')]
    private int $category_id;

    #[Column(type: 'integer')]
    private int $type;

    #[Column(type: 'integer')]
    private int $sort;

    #[Column(type: 'integer')]
    private int $object_id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $layer_id = null;

    #[ManyToOne(targetEntity: DatabaseType::class)]
    #[JoinColumn(name: 'type', nullable: false, referencedColumnName: 'id')]
    private DatabaseType $type_object;

    #[ManyToOne(targetEntity: DatabaseCategory::class, inversedBy: 'entries')]
    #[JoinColumn(name: 'category_id', nullable: false, referencedColumnName: 'id')]
    private DatabaseCategory $category;

    public function getId(): int
    {
        return $this->id;
    }

    public function setDescription(string $description): DatabaseEntry
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setData(string $data): DatabaseEntry
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setCategory(DatabaseCategory $category): DatabaseEntry
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): DatabaseCategory
    {
        return $this->category;
    }

    public function setSort(int $sort): DatabaseEntry
    {
        $this->sort = $sort;

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setObjectId(int $objectId): DatabaseEntry
    {
        $this->object_id = $objectId;

        return $this;
    }

    public function getObjectId(): int
    {
        return $this->object_id;
    }

    public function getTypeObject(): DatabaseType
    {
        return $this->type_object;
    }

    public function setTypeObject(DatabaseType $typeObject): DatabaseEntry
    {
        $this->type_object = $typeObject;

        return $this;
    }

    public function getCategoryId(): int
    {
        return $this->category_id;
    }

    public function getTypeId(): int
    {
        return $this->type;
    }

    public function getLayerId(): ?int
    {
        return $this->layer_id;
    }

    public function setLayerId(?int $layerId): DatabaseEntry
    {
        $this->layer_id = $layerId;

        return $this;
    }
}
