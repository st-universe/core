<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Map\MapEnum;
use Stu\Orm\Repository\UserLayerRepository;

#[Table(name: 'stu_user_layer')]
#[Entity(repositoryClass: UserLayerRepository::class)]
class UserLayer implements UserLayerInterface
{
    #[Column(type: 'smallint')]
    private int $map_type = MapEnum::MAPTYPE_INSERT;

    #[Id]
    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[Id]
    #[ManyToOne(targetEntity: 'Layer')]
    #[JoinColumn(name: 'layer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private LayerInterface $layer;

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function setUser(UserInterface $user): UserLayerInterface
    {
        $this->user = $user;

        return $this;
    }

    #[Override]
    public function getLayer(): LayerInterface
    {
        return $this->layer;
    }

    #[Override]
    public function setLayer(LayerInterface $layer): UserLayerInterface
    {
        $this->layer = $layer;

        return $this;
    }

    #[Override]
    public function getMappingType(): int
    {
        return $this->map_type;
    }

    #[Override]
    public function setMappingType(int $mappingType): UserLayerInterface
    {
        $this->map_type = $mappingType;

        return $this;
    }

    public function isExplored(): bool
    {
        return $this->getMappingType() === MapEnum::MAPTYPE_LAYER_EXPLORED;
    }
}
