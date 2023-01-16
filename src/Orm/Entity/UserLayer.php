<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Map\MapEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserLayerRepository")
 * @Table(
 *     name="stu_user_layer"
 * )
 **/
class UserLayer implements UserLayerInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     */
    private $user_id;

    /** 
     * @Id
     * @Column(type="integer")
     */
    private $layer_id;

    /** @Column(type="smallint") */
    private $map_type = MapEnum::MAPTYPE_INSERT;


    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="Layer")
     * @JoinColumn(name="layer_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $layer;

    public function getUser(): UserInterface
    {

        return $this->user;
    }

    public function setUser(UserInterface $user): UserLayerInterface
    {
        $this->user = $user;
        $this->user_id = $user->getId();

        return $this;
    }

    public function getLayer(): LayerInterface
    {

        return $this->layer;
    }

    public function setLayer(LayerInterface $layer): UserLayerInterface
    {
        $this->layer = $layer;
        $this->layer_id = $layer->getId();

        return $this;
    }

    public function getMappingType(): int
    {
        return $this->map_type;
    }

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
