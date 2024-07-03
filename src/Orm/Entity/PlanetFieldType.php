<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Orm\Repository\PlanetFieldTypeRepository;

#[Table(name: 'stu_colony_fieldtype')]
#[Index(name: 'field_id_idx', columns: ['field_id'])]
#[Entity(repositoryClass: PlanetFieldTypeRepository::class)]
class PlanetFieldType implements PlanetFieldTypeInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $field_id = 0;

    #[Column(type: 'string')]
    private string $description = '';

    #[Column(type: 'integer')]
    private int $normal_id = 0;

    #[Column(type: 'integer')]
    private int $category;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getFieldType(): int
    {
        return $this->field_id;
    }

    #[Override]
    public function setFieldType(int $fieldType): PlanetFieldTypeInterface
    {
        $this->field_id = $fieldType;

        return $this;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(string $description): PlanetFieldTypeInterface
    {
        $this->description = $description;

        return $this;
    }

    #[Override]
    public function getBaseFieldType(): int
    {
        return $this->normal_id;
    }

    #[Override]
    public function setBaseFieldType(int $baseFieldType): PlanetFieldTypeInterface
    {
        $this->normal_id = $baseFieldType;

        return $this;
    }

    #[Override]
    public function getCategory(): int
    {
        return $this->category;
    }
}
