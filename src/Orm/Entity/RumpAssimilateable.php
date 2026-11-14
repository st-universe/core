<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'stu_rump_assimilateable')]
class RumpAssimilateable
{
    #[Id]
    #[OneToOne(targetEntity: SpacecraftRump::class)]
    #[JoinColumn(name: 'rump_id', referencedColumnName: 'id', nullable: false)]
    private SpacecraftRump $rump;

    public function getRump(): SpacecraftRump
    {
        return $this->rump;
    }

    public function setRump(SpacecraftRump $rump): self
    {
        $this->rump = $rump;
        return $this;
    }
}
