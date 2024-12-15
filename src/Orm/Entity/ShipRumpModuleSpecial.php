<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Override;

#[Table(name: 'stu_rumps_module_special')]
#[Index(name: 'rump_module_special_ship_rump_idx', columns: ['rump_id'])]
#[Entity]
class ShipRumpModuleSpecial implements ShipRumpModuleSpecialInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer')]
    private int $module_special_id = 0;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setRumpId(int $rumpId): ShipRumpModuleSpecialInterface
    {
        $this->rump_id = $rumpId;

        return $this;
    }

    #[Override]
    public function getModuleSpecialId(): int
    {
        return $this->module_special_id;
    }

    #[Override]
    public function setModuleSpecialId(int $moduleSpecialId): ShipRumpModuleSpecialInterface
    {
        $this->module_special_id = $moduleSpecialId;

        return $this;
    }
}
