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
use Stu\Orm\Repository\ShipRumpSpecialRepository;

#[Table(name: 'stu_rumps_specials')]
#[Index(name: 'rump_special_ship_rump_idx', columns: ['rump_id'])]
#[Entity(repositoryClass: ShipRumpSpecialRepository::class)]
class ShipRumpSpecial
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer')]
    private int $special = 0;

    #[ManyToOne(targetEntity: SpacecraftRump::class, inversedBy: 'specialAbilities')]
    #[JoinColumn(name: 'rump_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?SpacecraftRump $spacecraftRump = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setRumpId(int $rumpId): ShipRumpSpecial
    {
        $this->rump_id = $rumpId;

        return $this;
    }

    public function getSpecialId(): int
    {
        return $this->special;
    }

    public function setSpecialId(int $specialId): ShipRumpSpecial
    {
        $this->special = $specialId;

        return $this;
    }
}
