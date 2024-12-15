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
use Override;
use Stu\Orm\Repository\ShipRumpSpecialRepository;

#[Table(name: 'stu_rumps_specials')]
#[Index(name: 'rump_special_ship_rump_idx', columns: ['rumps_id'])]
#[Entity(repositoryClass: ShipRumpSpecialRepository::class)]
class ShipRumpSpecial implements ShipRumpSpecialInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rumps_id = 0;

    #[Column(type: 'integer')]
    private int $special = 0;

    #[ManyToOne(targetEntity: 'SpacecraftRump', inversedBy: 'specialAbilities')]
    #[JoinColumn(name: 'rumps_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?SpacecraftRumpInterface $spacecraftRump = null;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setRumpId(int $rumpId): ShipRumpSpecialInterface
    {
        $this->rumps_id = $rumpId;

        return $this;
    }

    #[Override]
    public function getSpecialId(): int
    {
        return $this->special;
    }

    #[Override]
    public function setSpecialId(int $specialId): ShipRumpSpecialInterface
    {
        $this->special = $specialId;

        return $this;
    }
}
