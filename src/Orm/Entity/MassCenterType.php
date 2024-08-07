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
use Override;

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

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function getSize(): int
    {
        return $this->size;
    }

    #[Override]
    public function getFirstFieldType(): MapFieldTypeInterface
    {
        return $this->firstFieldType;
    }
}
