<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Map\MapEnum;

#[Table(name: 'stu_user_layer')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\UserLayerRepository')]
class UserLayer implements UserLayerInterface
{
    #[Id]
    #[Column(type: 'integer')]
    private int $user_id;

    #[Id]
    #[Column(type: 'integer')]
    private int $layer_id;

    #[Column(type: 'smallint')]
    private int $map_type = MapEnum::MAPTYPE_INSERT;

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

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
        $this->user_id = $user->getId();

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
        $this->layer_id = $layer->getId();

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
