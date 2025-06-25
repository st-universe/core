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
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\DatabaseEntryRepository;

#[Table(name: 'stu_database_entrys')]
#[Index(name: 'database_entry_category_id_idx', columns: ['category_id'])]
#[Entity(repositoryClass: DatabaseEntryRepository::class)]
class DatabaseEntry implements DatabaseEntryInterface
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
    private DatabaseTypeInterface $type_object;

    #[ManyToOne(targetEntity: DatabaseCategory::class, inversedBy: 'entries')]
    #[JoinColumn(name: 'category_id', nullable: false, referencedColumnName: 'id')]
    private DatabaseCategoryInterface $category;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setDescription(string $description): DatabaseEntryInterface
    {
        $this->description = $description;

        return $this;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setData(string $data): DatabaseEntryInterface
    {
        $this->data = $data;

        return $this;
    }

    #[Override]
    public function getData(): string
    {
        return $this->data;
    }

    #[Override]
    public function setCategory(DatabaseCategoryInterface $category): DatabaseEntryInterface
    {
        $this->category = $category;

        return $this;
    }

    #[Override]
    public function getCategory(): DatabaseCategoryInterface
    {
        return $this->category;
    }

    #[Override]
    public function setSort(int $sort): DatabaseEntryInterface
    {
        $this->sort = $sort;

        return $this;
    }

    #[Override]
    public function getSort(): int
    {
        return $this->sort;
    }

    #[Override]
    public function setObjectId(int $objectId): DatabaseEntryInterface
    {
        $this->object_id = $objectId;

        return $this;
    }

    #[Override]
    public function getObjectId(): int
    {
        return $this->object_id;
    }

    #[Override]
    public function getTypeObject(): DatabaseTypeInterface
    {
        return $this->type_object;
    }

    #[Override]
    public function setTypeObject(DatabaseTypeInterface $typeObject): DatabaseEntryInterface
    {
        $this->type_object = $typeObject;

        return $this;
    }

    #[Override]
    public function getCategoryId(): int
    {
        return $this->category_id;
    }

    #[Override]
    public function getTypeId(): int
    {
        return $this->type;
    }

    #[Override]
    public function getLayerId(): ?int
    {
        return $this->layer_id;
    }

    #[Override]
    public function setLayerId(?int $layerId): DatabaseEntryInterface
    {
        $this->layer_id = $layerId;

        return $this;
    }
}
