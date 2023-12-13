<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_mass_center_type')]
#[Index(name: 'mass_center_field_type_idx', columns: ['first_field_type_id'])]
#[Entity]
class MassCenterType implements MassCenterTypeInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $description = '';

    #[Column(type: 'integer')]
    private int $size = 1;

    #[Column(type: 'integer')]
    private int $first_field_type_id = 0;

    #[OneToOne(targetEntity: 'MapFieldType')]
    #[JoinColumn(name: 'first_field_type_id', referencedColumnName: 'id')]
    private MapFieldTypeInterface $firstFieldType;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getFirstFieldType(): MapFieldTypeInterface
    {
        return $this->firstFieldType;
    }
}
