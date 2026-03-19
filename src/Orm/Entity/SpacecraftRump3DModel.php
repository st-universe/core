<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\SpacecraftRump3DModelRepository;

#[Table(name: 'stu_rumps_3d_model')]
#[Entity(repositoryClass: SpacecraftRump3DModelRepository::class)]
class SpacecraftRump3DModel
{
    #[Id]
    #[OneToOne(targetEntity: SpacecraftRump::class, inversedBy: 'model3d')]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRump $rump;

    #[Column(type: 'integer')]
    private int $width = 0;

    #[Column(type: 'integer')]
    private int $height = 0;

    #[Column(type: 'integer')]
    private int $rotation = 0;

    public function getRump(): SpacecraftRump
    {
        return $this->rump;
    }

    public function setRump(SpacecraftRump $rump): SpacecraftRump3DModel
    {
        $this->rump = $rump;

        return $this;
    }

    public function getRumpId(): int
    {
        return $this->rump->getId();
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): SpacecraftRump3DModel
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): SpacecraftRump3DModel
    {
        $this->height = $height;

        return $this;
    }

    public function getRotation(): int
    {
        return $this->rotation;
    }

    public function setRotation(int $rotation): SpacecraftRump3DModel
    {
        $this->rotation = $rotation;

        return $this;
    }
}
