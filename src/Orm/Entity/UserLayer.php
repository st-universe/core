<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Map\MapEnum;
use Stu\Orm\Repository\UserLayerRepository;

#[Table(name: 'stu_user_layer')]
#[Entity(repositoryClass: UserLayerRepository::class)]
class UserLayer
{
    #[Column(type: 'smallint')]
    private int $map_type = MapEnum::MAPTYPE_INSERT;

    #[Id]
    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[Id]
    #[ManyToOne(targetEntity: Layer::class)]
    #[JoinColumn(name: 'layer_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Layer $layer;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): UserLayer
    {
        $this->user = $user;

        return $this;
    }

    public function getLayer(): Layer
    {
        return $this->layer;
    }

    public function setLayer(Layer $layer): UserLayer
    {
        $this->layer = $layer;

        return $this;
    }

    public function getMappingType(): int
    {
        return $this->map_type;
    }

    public function setMappingType(int $mappingType): UserLayer
    {
        $this->map_type = $mappingType;

        return $this;
    }
}
