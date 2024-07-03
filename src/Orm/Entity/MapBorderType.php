<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_map_bordertypes')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\MapBorderTypeRepository')]
class MapBorderType implements MapBorderTypeInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $faction_id = 0;

    #[Column(type: 'string')]
    private string $color = '';

    #[Column(type: 'string')]
    private string $description;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getFactionId(): int
    {
        return $this->faction_id;
    }

    #[Override]
    public function setFactionId(int $factionId): MapBorderTypeInterface
    {
        $this->faction_id = $factionId;

        return $this;
    }

    #[Override]
    public function getColor(): string
    {
        return $this->color;
    }

    #[Override]
    public function setColor(string $color): MapBorderTypeInterface
    {
        $this->color = $color;

        return $this;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setDescription(string $description): MapBorderTypeInterface
    {
        $this->description = $description;

        return $this;
    }
}
